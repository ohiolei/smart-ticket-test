<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class TicketClassifier
{
    public function classify(string $subject, string $body): array
    {
        Log::info("Classifying ticket - Subject: {$subject}");

        // Rate limiting: max 3 requests per minute
        $rateLimitKey = 'openai_rate_limit_' . date('Y-m-d-H-i');
        $requestsThisMinute = Cache::get($rateLimitKey, 0);
        
        if ($requestsThisMinute >= 3) {
            Log::warning('OpenAI rate limit exceeded, using fallback');
            return $this->getDummyClassification();
        }

        Cache::put($rateLimitKey, $requestsThisMinute + 1, 60); // Expire in 60 seconds

        if (!config('services.openai.classify_enabled', false)) {
            Log::info("Using dummy classification (OpenAI disabled)");
            return $this->getDummyClassification();
        }

        if (empty(config('services.openai.api_key'))) {
            Log::error('OpenAI API key is not configured');
            return $this->getDummyClassification();
        }

        try {
            Log::info("Making OpenAI API request");
            
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a support ticket classification system. Analyze the following ticket and respond with ONLY valid JSON containing these keys: category (string, one of: billing, bug, feature, account, other), explanation (string), confidence (float between 0 and 1). Do not include any other text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Ticket Subject: $subject\n\nTicket Body: $body"
                    ]
                ],
                'temperature' => 0.1,
                'max_tokens' => 150,
            ]);

            Log::info("OpenAI response received");
            
            $content = $response->choices[0]->message->content;
            $content = preg_replace('/```json|```/', '', $content);
            $content = trim($content);

            $result = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('OpenAI returned invalid JSON: ' . $content);
                return $this->getDummyClassification();
            }

            if (!isset($result['category']) || !isset($result['explanation']) || !isset($result['confidence'])) {
                Log::error('OpenAI response missing required fields');
                return $this->getDummyClassification();
            }

            $allowedCategories = ['billing', 'bug', 'feature', 'account', 'other'];
            if (!in_array($result['category'], $allowedCategories)) {
                Log::warning('OpenAI returned invalid category: ' . $result['category']);
                $result['category'] = 'other';
            }

            $result['confidence'] = max(0, min(1, (float)$result['confidence']));

            Log::info("Classification successful");
            return $result;

        } catch (Exception $e) {
            Log::error('OpenAI classification failed: ' . $e->getMessage());
            return $this->getDummyClassification();
        }
    }

    private function getDummyClassification(): array
    {
        $categories = ['billing', 'bug', 'feature', 'account', 'other'];
        $category = $categories[array_rand($categories)];
        
        $explanations = [
            'billing' => 'Related to payment or subscription issues.',
            'bug' => 'Technical issue or software bug report.',
            'feature' => 'Feature request or enhancement suggestion.',
            'account' => 'Account management or access related issue.',
            'other' => 'General inquiry that doesn\'t fit other categories.'
        ];
        
        return [
            'category' => $category,
            'explanation' => $explanations[$category],
            'confidence' => rand(70, 95) / 100
        ];
    }
}