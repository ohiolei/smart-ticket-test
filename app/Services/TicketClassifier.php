<?php

namespace App\Services;

use App\Models\Ticket;
use OpenAI\Laravel\Facades\OpenAI;

class TicketClassifier
{
    public function classify(Ticket $ticket): array
    {
        if (!filter_var(config('services.classifier.enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            // fallback dummy
            $cats = ['billing','bug','feature','account','other'];
            $c = $cats[array_rand($cats)];
            return [
                'category'     => $ticket->category ?? $c,
                'explanation'  => "Dummy classification for testing: {$c}.",
                'confidence'   => round(mt_rand(55, 95) / 100, 2),
            ];
        }

        $model = config('services.classifier.model', 'gpt-4o-mini');

        $system = <<<SYS
You are an assistant that classifies help-desk tickets.
Return ONLY strict JSON with keys: category, explanation, confidence (0..1).
Allowed categories: ["billing","bug","feature","account","other"].
SYS;

        $user = "Subject: {$ticket->subject}\nBody: {$ticket->body}";

        $resp = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'temperature' => 0.2,
        ]);

        $content = trim($resp->choices[0]->message->content ?? '{}');
        $json = json_decode($content, true);
        // basic guardrails
        $category = $json['category'] ?? 'other';
        $confidence = isset($json['confidence']) ? (float)$json['confidence'] : 0.6;
        $explanation = $json['explanation'] ?? 'Not provided';

        return compact('category','explanation','confidence');
    }
}
