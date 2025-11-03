<?php

namespace Boy132\Billing\Console\Commands;

use Boy132\Billing\Models\Order;
use Illuminate\Console\Command;

class CheckOrdersCommand extends Command
{
    protected $signature = 'p:billing:check-orders';

    protected $description = 'Checks the expire date for orders.';

    public function handle(): int
    {
        $orders = Order::all();

        if ($orders->count() < 1) {
            $this->line('No orders');

            return 0;
        }

        $bar = $this->output->createProgressBar(count($orders));
        foreach ($orders as $order) {
            $bar->clear();

            $order->checkExpire();

            $bar->advance();
            $bar->display();
        }

        $this->line('');

        return 0;
    }
}
