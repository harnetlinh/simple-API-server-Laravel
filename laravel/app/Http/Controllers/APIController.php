<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $message = $request->message;
            $response = $this->cozeHandler($message);
            return response()->json([
                'message' => $response
            ]);
        } catch (\Throwable $th) {
            Log::error("Error in chat: " . $th->getMessage());
            return response()->json([
                'message' => "Error in chat: " . $th->getMessage()
            ]);
        }
    }
    /**
     * Handle message with Coze API
     * @param string $message
     * @return string
     */
    private function cozeHandler($message)
    {
        $response = "";
        try {
            $body = [
                "bot_id" => env('COZE_BOT_ID', ''),
                "user" => "linhhn13",
                "query" => $message,
                "stream" => false
            ];
            $headers = [
                'Content-Type' => 'application/json',
                'Connection' => 'keep-alive',
                'Accept' => '*/*',
                'Authorization' => 'Bearer ' . env('COZE_TOKEN', '')
            ];
            $response = Http::withHeaders(
                $headers
            )->post(env('COZE_API_URL', ''), $body)->json();
            Log::error("Response: " . json_encode($response, JSON_PRETTY_PRINT));
            if (isset($response['messages'])) {
                if (count($response['messages']) > 0) {
                    $response = $response['messages'][0];
                    if (isset($response['content'])) {
                        $response = $response['content'];
                    }
                }
            }
            return $response;
        } catch (\Throwable $th) {
            Log::error("Error in cozeHandler: ". $th->getLine() . ' - ' . $th->getMessage());
            throw new Exception("Error in cozeHandler: " . $th->getLine() . ' - ' . $th->getMessage());
        }
    }
}
