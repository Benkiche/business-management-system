<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Send message.
     */
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        // Check authorization
        if (!$conversation->canParticipate(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if conversation is closed
        if ($conversation->status === 'closed') {
            return response()->json(['error' => 'This conversation is closed'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'type' => 'in:text,note|nullable',
        ]);

        try {
            $message = $conversation->messages()->create([
                'user_id' => auth()->id(),
                'body' => $validated['body'],
                'type' => $validated['type'] ?? 'text',
            ]);

            // Update conversation's last message time
            $conversation->update(['last_message_at' => now()]);

            // Mark as read for sender
            $message->markAsRead(auth()->user());

            AuditService::log(
                'created',
                'Message',
                $message->id,
                "Sent message in conversation: {$conversation->subject}"
            );

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'sender_name' => $message->sender_name,
                    'sender_type' => $message->sender_type,
                    'created_at' => $message->created_at->diffForHumans(),
                    'created_at_full' => $message->created_at->format('M d, Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update message.
     */
    public function update(Request $request, Message $message): JsonResponse
    {
        // Check authorization
        if (!$message->canBeEditedBy(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        try {
            $message->update([
                'body' => $validated['body'],
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            AuditService::log(
                'updated',
                'Message',
                $message->id,
                'Edited message'
            );

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'is_edited' => $message->is_edited,
                    'edited_at' => $message->edited_at->format('M d, Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete message.
     */
    public function destroy(Message $message): JsonResponse
    {
        if (!$message->canBeDeletedBy(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $message->delete();

            AuditService::log(
                'deleted',
                'Message',
                $message->id,
                'Deleted message'
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(Message $message): JsonResponse
    {
        if (!$message->conversation->canParticipate(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->markAsRead(auth()->user());

        return response()->json(['success' => true]);
    }

    /**
     * Get messages (AJAX pagination).
     */
    public function getMessages(Conversation $conversation, Request $request): JsonResponse
    {
        if (!$conversation->canParticipate(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        $messages = $conversation->messages()
            ->with(['sender', 'reads'])
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'messages' => $messages->items(),
            'pagination' => [
                'total' => $messages->total(),
                'per_page' => $messages->perPage(),
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    /**
     * Get read receipts for message.
     */
    public function getReadReceipts(Message $message): JsonResponse
    {
        if (!$message->conversation->canParticipate(auth()->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reads = $message->reads()->with('user')->get();

        return response()->json([
            'read_count' => $reads->count(),
            'readers' => $reads->map(fn($read) => [
                'name' => $read->user->name,
                'avatar' => $read->user->getAvatar(),
                'read_at' => $read->read_at->diffForHumans(),
            ]),
        ]);
    }
}