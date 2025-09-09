<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Jobs\ClassifyTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        
        if ($search = $request->string('query')->toString()) {
           Ticket::where(function($query_con) use ($search) {
                $query_con->where('subject','like',"%$search%")
                  ->orWhere('body','like',"%$search%");
            });
        }

        if ($status = $request->string('status')->toString()) {
           Ticket::where('status', $status);
        }

        if ($category = $request->string('category')->toString()) {
           Ticket::where('category', $category);
        }

        $perPage = (int) $request->get('per_page', 10);
        $tickets = Ticket::latest()->paginate($perPage);

        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $ticket = Ticket::create($data);

        return response()->json($ticket, 201);
    }

    public function show(string $id)
    {
        return Ticket::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $data = $request->validate([
            'status'   => 'nullable|in:open,in_progress,closed',
            'category' => 'nullable|string|max:50',
            'note'     => 'nullable|string',
        ]);

        $ticket->update($data);

        return response()->json($ticket);
    }

    public function classify(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        ClassifyTicket::dispatch($ticket->id);

        return response()->json([
            'queued' => true,
            'ticket' => $ticket,
        ], 202);
    }
}

