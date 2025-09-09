<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Jobs\ClassifyTicket;

class BulkClassifyTickets extends Command
{
    protected $signature = 'tickets:bulk-classify {--all}';
    protected $description = 'Queue classification jobs for tickets. Use --all to reclassify everything.';

    public function handle(): int
    {
        $query = $this->option('all') ? Ticket::query() : Ticket::whereNull('confidence');
        $ids = $query->pluck('id');

        foreach ($ids as $id) {
            ClassifyTicket::dispatch($id);
        }

        $this->info("Queued " . $ids->count() . " classification jobs.");
        return self::SUCCESS;
    }
}
