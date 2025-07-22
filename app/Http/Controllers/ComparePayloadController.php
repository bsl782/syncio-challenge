<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ComparePayloadController extends Controller
{
    public function storeAndCompare(Request $request)
    {
        $payload = $request->json()->all();
        $cache = Cache::store('file');

        //Checks cache if payloads exists if not defaults to []
        $storedPayloads = $cache->get('payloads', []);
        $storedPayloads[] = $payload;

        //Stores in cache for a minute
        $cache->put('payloads', $storedPayloads, $seconds = 60);

        //Should only trigger for first payload
        if (count($storedPayloads) < 2) {
            return response()->json(['hasOnlyOneItem' => true]);
        }

        //Compare the two payloads and generate a structured diff
        $diffPayload = $this->getStructuredDiff($storedPayloads[0], $storedPayloads[1]);

        //Clears cache after diff payload is created
        $cache->forget('payloads');

        return response()->json([
            'diff' => $diffPayload
        ]);
    }

    private function getStructuredDiff($firstPayload, $secondPayload)
    {
        $diff = [];

        // If both values are arrays recurse further
        if (is_array($firstPayload) && is_array($secondPayload)) {

            // Check for added or changed keys
            // Grabs keys from the secondPayload to compare to the first
            foreach ($secondPayload as $key => $secondVal) {
                if (array_key_exists($key, $firstPayload)) {
                    $firstVal = $firstPayload[$key];

                    // If both values are arrays, recurse deeper
                    if (is_array($secondVal) && is_array($firstVal)) {
                        $subDiff = $this->getStructuredDiff($firstVal, $secondVal);

                        if (!empty($subDiff)) {
                            $diff[$key] = [
                                'type' => 'nested',
                                'children' => $subDiff,
                            ];
                        }
                    }
                    // If values are different add mark
                    elseif ($firstVal !== $secondVal) {
                        $diff[$key] = [
                            'type' => 'changed',
                            'from' => $firstVal,
                            'to' => $secondVal,
                        ];
                    }
                } else {
                    // Key exists only in the second payload change to added
                    $diff[$key] = [
                        'type' => 'added',
                        'value' => $secondVal,
                    ];
                }
            }

            // Step 2: Check for removed keys
            foreach ($firstPayload as $key => $origVal) {
                if (!array_key_exists($key, $secondPayload)) {
                    $diff[$key] = [
                        'type' => 'removed',
                        'value' => $origVal,
                    ];
                }
            }
        }
        // If both items are just values do a comparison
        else {
            if ($firstPayload !== $secondPayload) {
                $diff = [
                    'type' => 'changed',
                    'from' => $firstPayload,
                    'to' => $secondPayload,
                ];
            }
        }

        return $diff;
    }
}
