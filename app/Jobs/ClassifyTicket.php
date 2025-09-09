<?php
namespace App\Jobs;

use App\Models\Ticket;
use App\Services\TicketClassifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ClassifyTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $ticketId) {}

    public function handle(TicketClassifier $classifier): void
    {
        $ticket = Ticket::findOrFail($this->ticketId);

        $result = $classifier->classify($ticket);

        // Keep manual category if user already changed it
        $newCategory = $ticket->category ?: $result['category'];

        $ticket->update([
            'category'    => $newCategory,
            'explanation' => $result['explanation'],
            'confidence'  => $result['confidence'],
        ]);
    }
}
