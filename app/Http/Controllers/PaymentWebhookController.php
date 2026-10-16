<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentManager;
use App\Services\PaymentReconciliationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function stripe(Request $request, PaymentManager $manager, PaymentReconciliationService $reconciliation): Response
    {
        $payload = $request->getContent();
        $event = $manager->gateway()->verifyWebhook($payload, (string) $request->header('Stripe-Signature'));
        return response(['received' => true] + $reconciliation->handle($event));
    }
}
