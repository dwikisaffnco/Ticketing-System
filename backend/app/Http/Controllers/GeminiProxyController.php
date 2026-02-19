<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProxyController extends Controller
{
    /**
     * Proxies a chat request to the Gemini API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        $modelId = config('services.gemini.model') ?? env('GEMINI_MODEL_ID', 'gemini-2.0-flash-lite-preview-02-05');

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key not configured.'], 500);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelId}:generateContent?key={$apiKey}";

        $history = $request->input('history', []);
        $contents = [];

        // Build contents for Gemini API (user message + history)
        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $item['content']]]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $request->input('message')]]
        ];

        try {
            $response = Http::post($url, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json(['error' => 'Failed to get response from AI.'], 500);
            }

            $data = $response->json();
            $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from AI.';

            return response()->json([
                'response' => $aiResponse
            ]);

        } catch (\Exception $e) {
            Log::error('Gemini Proxy Exception: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during AI processing.'], 500);
        }
    }
}
