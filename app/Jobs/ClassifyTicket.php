<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClassifyTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1 min, 5 min, 10 min

    public function __construct(public string $ticketId) {}

    public function handle(TicketClassifier $classifier): void
    {
        $ticket = Ticket::findOrFail($this->ticketId);

        try {
            $classification = $classifier->classify(
                $ticket->subject,
                $ticket->body ?? ''
            );

            $ticket->update([
                'category' => $classification['category'],
                'explanation' => $classification['explanation'],
                'confidence' => $classification['confidence']
            ]);

        } catch (\Exception $e) {
            Log::error("Ticket classification failed: " . $e->getMessage());
            
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1]);
                return;
            }
            
            $ticket->update([
                'explanation' => 'Classification failed after multiple attempts',
                'confidence' => 0
            ]);
        }
    }
}