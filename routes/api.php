<?php

use App\Http\Controllers\Api\WebhookInboundController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/access', [WebhookInboundController::class, 'handle'])->name('webhooks.inbound');
