<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class TestOpenAI extends Command
{
    protected $signature = 'test:openai';
    protected $description = 'Test OpenAI API connection';

    public function handle()
    {
        $this->info('Testing OpenAI connection...');
        
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, are you working?']
                ],
                'max_tokens' => 10,
            ]);
            
            $this->info('OpenAI connection successful!');
            $this->info('Response: ' . $response->choices[0]->message->content);
            
        } catch (\Exception $e) {
            $this->error('OpenAI connection failed: ' . $e->getMessage());
            $this->error('Make sure your API key is set in .env as OPENAI_API_KEY');
        }
    }
}