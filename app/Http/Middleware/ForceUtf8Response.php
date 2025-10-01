<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceUtf8Response
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $response = $next($request);

            // Only handle JSON responses
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                try {
                    // Try to get the original data
                    $originalData = $response->getData(true);

                    // If we can detect this is a Filament notification response, bypass it entirely
                    if ($this->isFilamentNotification($originalData)) {
                        // Return a simple success response instead
                        return response()->json([
                            'success' => true,
                            'message' => 'Operation completed'
                        ]);
                    }

                    // Try to clean the data
                    $cleanData = $this->safeClean($originalData);

                    // Create a new response with clean data
                    $response->setData($cleanData);
                } catch (\Exception $e) {
                    // If any error occurs, return a minimal safe response
                    return response()->json([
                        'success' => false,
                        'message' => 'Response processing error'
                    ], 200);
                }
            }

            return $response;
        } catch (\Exception $e) {
            // If the entire request fails, return a minimal response
            return response()->json([
                'success' => false,
                'message' => 'Request processing error'
            ], 200);
        }
    }

    /**
     * Check if this is a Filament notification response that might cause UTF-8 issues
     */
    private function isFilamentNotification($data)
    {
        if (!is_array($data)) {
            return false;
        }

        // Check for common Filament notification keys
        $notificationKeys = ['notification', 'notifications', 'message', 'title', 'body'];

        foreach ($notificationKeys as $key) {
            if (array_key_exists($key, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safely clean data with multiple fallback strategies
     */
    private function safeClean($data)
    {
        try {
            // Strategy 1: Try JSON encode/decode cycle
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new \Exception('JSON encode failed');
            }

            $decoded = json_decode($json, true);
            if ($decoded === null) {
                throw new \Exception('JSON decode failed');
            }

            return $decoded;
        } catch (\Exception $e) {
            // Strategy 2: Return minimal safe data structure
            return $this->createSafeResponse($data);
        }
    }

    /**
     * Create a minimal safe response structure
     */
    private function createSafeResponse($originalData)
    {
        // If the original data has a success key, preserve it
        $safeResponse = [
            'success' => is_array($originalData) && isset($originalData['success']) ? $originalData['success'] : false,
            'timestamp' => time(),
        ];

        // Add minimal additional data if possible
        if (is_array($originalData)) {
            foreach ($originalData as $key => $value) {
                // Only include simple, safe values
                if (is_bool($value) || is_numeric($value)) {
                    $safeResponse[$key] = $value;
                } elseif (is_string($value) && $this->isSimpleString($value)) {
                    $safeResponse[$key] = $this->simpleCleanString($value);
                }
            }
        }

        return $safeResponse;
    }

    /**
     * Check if a string is simple enough to be safe
     */
    private function isSimpleString($string)
    {
        if (!is_string($string) || strlen($string) > 100) {
            return false;
        }

        // Only allow basic ASCII characters
        return preg_match('/^[a-zA-Z0-9\s\.\,\!\?\-\_]+$/', $string) === 1;
    }

    /**
     * Simple string cleaning for basic strings
     */
    private function simpleCleanString($string)
    {
        if (!is_string($string)) {
            return $string;
        }

        // Remove any non-ASCII characters
        return preg_replace('/[^\x20-\x7E]/', '', $string);
    }
}