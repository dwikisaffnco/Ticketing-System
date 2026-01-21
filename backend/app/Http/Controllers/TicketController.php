<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketUpdateRequest;
use App\Http\Requests\TicketReplyStoreRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\TicketReplyResource;
use App\Http\Resources\TicketResource;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Ticket::query();

            // Default: hide archived tickets unless explicitly requested
            if ($request->has('archived')) {
                if ((string) $request->archived === '1') {
                    $query->whereNotNull('archived_at');
                }
                if ((string) $request->archived === '0') {
                    $query->whereNull('archived_at');
                }
            } else {
                $query->whereNull('archived_at');
            }

            $query->orderBy('created_at', 'desc');

            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            if (auth()->user()->role == 'user') {
                $query->where('user_id', auth()->user()->id);
            }

            $perPage = $request->get('per_page', 10);
            $tickets = $query->paginate($perPage);
 
            return response()->json([
                'message' => 'Data Tiket Berhasil Ditampilkan',
                'data' => TicketResource::collection($tickets),
                'meta' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
                'links' => [
                    'first' => $tickets->url(1),
                    'last' => $tickets->url($tickets->lastPage()),
                    'prev' => $tickets->previousPageUrl(),
                    'next' => $tickets->nextPageUrl(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => null
            ], 500);
        }
    }

    public function archive($code)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $ticket->archived_at = now();
            $ticket->save();

            ActivityLog::record(
                'ticket_archive',
                'Ticket archived',
                ['ticket_code' => $ticket->code, 'ticket_id' => $ticket->id],
                request()
            );

            return response()->json([
                'message' => 'Tiket berhasil di-archive',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function unarchive($code)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $ticket->archived_at = null;
            $ticket->save();

            ActivityLog::record(
                'ticket_unarchive',
                'Ticket unarchived',
                ['ticket_code' => $ticket->code, 'ticket_id' => $ticket->id],
                request()
            );

            return response()->json([
                'message' => 'Tiket berhasil dikembalikan',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan mengakses tiket ini'
                ], 403);
            }

            return response()->json([
                'message' => 'Tiket Berhasil Ditampilkan',
                'data' => new TicketResource($ticket)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = new Ticket;
            $ticket->user_id = auth()->user()->id;
            $ticket->code = 'TIC-' . rand(10000, 99999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];

            if ($request->hasFile('attachment')) {
                $ticket->attachment_path = $request->file('attachment')->store('tickets', 'attachments');
            }

            $ticket->save();

            ActivityLog::record(
                'ticket_create',
                'Ticket created',
                ['ticket_code' => $ticket->code, 'ticket_id' => $ticket->id, 'title' => $ticket->title],
                $request
            );

            // POV User: create ticket -> notify admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketEventNotification([
                    'title' => 'Tiket baru dibuat',
                    'message' => 'Tiket ' . $ticket->code . ' telah dibuat oleh ' . auth()->user()->name . '.',
                    'href' => [
                        'name' => 'admin.ticket.detail',
                        'params' => ['code' => $ticket->code],
                    ],
                    'meta' => [
                        'ticketCode' => $ticket->code,
                        'event' => 'ticket_created',
                        'actorName' => auth()->user()->name,
                        'content' => $ticket->description,
                    ],
                ]));
            }

            DB::commit();

            return response()->json([
                'message' => 'Tiket berhasil ditambahkan',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function update(TicketUpdateRequest $request, $code)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];

            if ($request->hasFile('attachment')) {
                if ($ticket->attachment_path) {
                    Storage::disk('attachments')->delete($ticket->attachment_path);
                }

                $ticket->attachment_path = $request->file('attachment')->store('tickets', 'attachments');
            }

            $ticket->save();

            ActivityLog::record(
                'ticket_update',
                'Ticket updated',
                ['ticket_code' => $ticket->code, 'ticket_id' => $ticket->id, 'title' => $ticket->title],
                $request
            );

            DB::commit();

            return response()->json([
                'message' => 'Tiket berhasil diperbarui',
                'data' => new TicketResource($ticket),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($code)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden',
                'data' => null,
            ], 403);
        }

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->with('ticketReplies')->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                    'data' => null,
                ], 404);
            }

            if ($ticket->attachment_path) {
                Storage::disk('public')->delete($ticket->attachment_path);
            }

            foreach (($ticket->ticketReplies ?? []) as $reply) {
                if ($reply->attachment_path) {
                    Storage::disk('public')->delete($reply->attachment_path);
                }
            }

            TicketReply::where('ticket_id', $ticket->id)->delete();
            $ticket->delete();

            ActivityLog::record(
                'ticket_delete',
                'Ticket deleted',
                ['ticket_code' => $code, 'ticket_id' => $ticket->id],
                request()
            );

            DB::commit();

            return response()->json([
                'message' => 'Tiket berhasil dihapus',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeReply(TicketReplyStoreRequest $request, $code)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan membalas tiket ini'
                ], 403);
            }

            $ticketReply = new TicketReply();
            $ticketReply->ticket_id = $ticket->id;
            $ticketReply->user_id = auth()->user()->id;
            $ticketReply->content = $data['content'];

            if ($request->hasFile('attachment')) {
                $ticketReply->attachment_path = $request->file('attachment')->store('ticket-replies', 'attachments');
            }

            $ticketReply->save();

            ActivityLog::record(
                auth()->user()->role === 'admin' ? 'ticket_reply_admin' : 'ticket_reply_user',
                'Ticket reply created',
                ['ticket_code' => $ticket->code, 'ticket_id' => $ticket->id, 'reply_id' => $ticketReply->id],
                $request
            );

            $actorRole = auth()->user()->role;

            // POV User: user reply -> notify admins
            if ($actorRole == 'user') {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new TicketEventNotification([
                        'title' => 'Balasan tiket dari user',
                        'message' => 'User membalas tiket ' . $ticket->code . '.',
                        'href' => [
                            'name' => 'admin.ticket.detail',
                            'params' => ['code' => $ticket->code],
                        ],
                        'meta' => [
                            'ticketCode' => $ticket->code,
                            'event' => 'user_replied',
                            'actorName' => auth()->user()->name,
                            'content' => $ticketReply->content,
                        ],
                    ]));
                }
            }

            // POV Admin: admin reply -> notify ticket owner
            if ($actorRole == 'admin') {
                $owner = $ticket->user;
                if ($owner) {
                    $owner->notify(new TicketEventNotification([
                        'title' => 'Balasan dari Team IT',
                        'message' => 'Admin membalas tiket ' . $ticket->code . '.',
                        'href' => [
                            'name' => 'app.ticket.detail',
                            'params' => ['code' => $ticket->code],
                        ],
                        'meta' => [
                            'ticketCode' => $ticket->code,
                            'event' => 'admin_replied',
                            'actorName' => auth()->user()->name,
                            'content' => $ticketReply->content,
                        ],
                    ]));
                }

                // POV Admin: admin changes status -> notify ticket owner
                $oldStatus = $ticket->status;
                $newStatus = $data['status'];

                $ticket->status = $newStatus;
                if ($newStatus == 'resolved') {
                    $ticket->completed_at = now();
                }
                $ticket->save();

                if ($oldStatus !== $newStatus && $owner) {
                    $owner->notify(new TicketEventNotification([
                        'title' => 'Status tiket diperbarui',
                        'message' => 'Status tiket ' . $ticket->code . ' berubah menjadi ' . $newStatus . '.',
                        'href' => [
                            'name' => 'app.ticket.detail',
                            'params' => ['code' => $ticket->code],
                        ],
                        'meta' => [
                            'ticketCode' => $ticket->code,
                            'event' => 'status_changed',
                            'oldStatus' => $oldStatus,
                            'newStatus' => $newStatus,
                            'actorName' => auth()->user()->name,
                        ],
                    ]));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Balasan berhasil ditambahkan',
                'data' => new TicketReplyResource($ticketReply)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTicketAttachment($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan mengakses tiket ini'
                ], 403);
            }

            if (!$ticket->attachment_path) {
                return response()->json([
                    'message' => 'Tiket tidak memiliki lampiran'
                ], 404);
            }

            $filePath = storage_path('app/attachments/' . $ticket->attachment_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $filename = 'ticket-' . $ticket->code . '-attachment.' . $extension;

            return response()->download($filePath, $filename);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTicketReplyAttachment($code, $replyId)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan mengakses tiket ini'
                ], 403);
            }

            $ticketReply = TicketReply::where('id', $replyId)
                ->where('ticket_id', $ticket->id)
                ->first();

            if (!$ticketReply) {
                return response()->json([
                    'message' => 'Balasan tiket tidak ditemukan'
                ], 404);
            }

            if (!$ticketReply->attachment_path) {
                return response()->json([
                    'message' => 'Balasan tiket tidak memiliki lampiran'
                ], 404);
            }

            $filePath = storage_path('app/attachments/' . $ticketReply->attachment_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $filename = 'ticket-' . $ticket->code . '-reply-' . $ticketReply->id . '-attachment.' . $extension;

            return response()->download($filePath, $filename);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function serveTicketAttachment($code)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan mengakses tiket ini'
                ], 403);
            }

            if (!$ticket->attachment_path) {
                return response()->json([
                    'message' => 'Tiket tidak memiliki lampiran'
                ], 404);
            }

            $filePath = storage_path('app/attachments/' . $ticket->attachment_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            return response()->file($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function serveTicketReplyAttachment($code, $replyId)
    {
        try {
            $ticket = Ticket::where('code', $code)->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan'
                ], 404);
            }

            if (auth()->user()->role == 'user' && $ticket->user_id != auth()->user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan mengakses tiket ini'
                ], 403);
            }

            $ticketReply = TicketReply::where('id', $replyId)
                ->where('ticket_id', $ticket->id)
                ->first();

            if (!$ticketReply) {
                return response()->json([
                    'message' => 'Balasan tiket tidak ditemukan'
                ], 404);
            }

            if (!$ticketReply->attachment_path) {
                return response()->json([
                    'message' => 'Balasan tiket tidak memiliki lampiran'
                ], 404);
            }

            $filePath = storage_path('app/attachments/' . $ticketReply->attachment_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            return response()->file($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
