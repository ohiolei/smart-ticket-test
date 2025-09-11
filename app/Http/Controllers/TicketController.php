<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Jobs\ClassifyTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TicketController extends Controller
{
    public function index(Request $request)
    {

        // // sever side filtering
        // $tickets = Ticket::when(
        //     $request->filled('subject') || $request->filled('status') || $request->filled('category'),
        //     function ($query) use ($request) {
        //         $query->where(function ($query) use ($request) {

        //             if ($request->filled('subject')) {
        //                 $query->where('subject', 'like', '%' . $request->input('subject') . '%');
        //             }

        //             if ($request->filled('status')) {
        //                 $query->where('status', $request->input('status'));
        //             }

        //             if ($request->filled('category')) {
        //                 $query->where('category', $request->input('category'));
        //             }
        //         });
        //     }
        // )
        //     ->paginate(10); 
       


        //will be filtered on client side
        $tickets = Ticket::all();


        return response()->json($tickets, 200);
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'nullable|string|max:15',
            'status' => 'required|string|max:255',
            'confidence' => 'nullable|numeric|between:0,1',
            'body' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500',
        ]);


        $ticket = Ticket::create($data);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket
        ]);
    }

    public function show(string $id)
    {
        return Ticket::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'status' => 'required|string|max:50',
            'confidence' => 'nullable|numeric',
            'body' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $ticket->update($data);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ]);
    }

    public function categorySummary()
    {
        $summary = Ticket::select('category', \DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json($summary);
    }

    public function statusSummary()
    {
        $summary = Ticket::select('status', \DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json($summary);
    }

    public function stats()
    {
        return response()->json([
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'pending_tickets' => Ticket::where('status', 'in_progress')->count(),
            'resolved_tickets' => Ticket::where('status', 'closed')->count(),
        ]);
    }


    public function classify(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Check if already classified
        if ($ticket->confidence !== null) {
            return response()->json([
                'message' => 'This ticket has already been classified',
                'ticket' => $ticket,
            ], 409);
        }

        // Check global rate limit
        $globalRateLimitKey = 'openai_global_rate_limit';
        $requestsToday = Cache::get($globalRateLimitKey, 0);

        if ($requestsToday >= 50) { // Limit to 50 requests per day
            return response()->json([
                'message' => 'Daily classification limit reached. Please try again tomorrow.',
                'ticket' => $ticket,
            ], 429);
        }

        Cache::put($globalRateLimitKey, $requestsToday + 1, 86400); // 24 hours

        // Dispatch the job
        ClassifyTicket::dispatch($ticket->id);

        return response()->json([
            'queued' => true,
            'message' => 'Classification job queued successfully',
            'ticket' => $ticket,
        ], 202);
    }

}

