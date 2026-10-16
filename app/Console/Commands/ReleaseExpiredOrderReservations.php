<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class ReleaseExpiredOrderReservations extends Command
{
    protected $signature = 'homeease:release-expired-orders';
    protected $description = 'Release inventory reservations for expired unpaid orders.';

    public function handle(OrderService $orders): int
    {
        $released = $orders->releaseExpiredReservations();
        $this->info("Released {$released} expired order reservation(s).");
        return self::SUCCESS;
    }
}
