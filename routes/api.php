<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ComparePayloadController;

Route::post('/compare-payloads', [ComparePayloadController::class, 'storeAndCompare']);
