<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use OpenTok\OpenTok;
use OpenTok\OpenTokException;
use App\Models\Room;

class RoomController extends Controller
{
    protected $opentok;
    protected $key; 
    protected $secret;

    public function __construct()
    {
        $this->key = config('services.vonage.key');   // Fetch from services.php
        $this->secret = config('services.vonage.secret'); // Fetch from services.php
        $this->opentok = new OpenTok($this->key, $this->secret);
    }

    public function joinRoom(Request $request)
    {
        // Validate request
        $request->validate([
            'room_name' => 'sometimes|string|max:255', // Make room_name optional
            'role' => 'required|in:publisher,subscriber' // Validate role
        ]);
    
        // Log the incoming request for debugging
        Log::info('Received join room request:', $request->all());

        // Retrieve or create a new room
        $roomName = $request->input('room_name', 'teleconsult'); // Default room name
        $room = Room::where('name', $roomName)->first();
    
        if (!$room) {
            try {
                $sessionId = $this->opentok->createSession()->getSessionId();
                $room = Room::create([
                    'name' => $roomName,
                    'session_id' => $sessionId,
                ]);
                Log::info("Created new room: $roomName with session ID: $sessionId");
            } catch (OpenTokException $e) {
                Log::error('Failed to create session: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to create session: ' . $e->getMessage()], 500);
            }
        } else {
            Log::info("Joined existing room: $roomName with session ID: " . $room->session_id);
        }
    
        // Generate a token for the session based on the role
        try {
            $role = $request->input('role') === 'publisher' ? \OpenTok\Role::PUBLISHER : \OpenTok\Role::SUBSCRIBER;
            $token = $this->opentok->generateToken($room->session_id, [
                'role' => $role,  // PUBLISHER or SUBSCRIBER
                'expireTime' => time() + 3600,       // 1 hour token expiry
                'data' => 'username=Guest'           // Optional metadata
            ]);
        } catch (OpenTokException $e) {
            Log::error('Failed to generate token: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate token: ' . $e->getMessage()], 500);
        }
    
        // Return the room details, including the session ID and token
        return response()->json([
            'room_name' => $roomName,
            'session_id' => $room->session_id,
            'token' => $token,
        ])
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
}
