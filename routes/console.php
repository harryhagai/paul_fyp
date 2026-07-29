<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\ClickPesaOrderSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clickpesa:sync-orders {--limit=50}', function (ClickPesaOrderSyncService $syncService) {
    $limit = max(1, (int) $this->option('limit'));
    $synced = $syncService->syncAllProcessingOrders($limit);

    $this->info("Synced {$synced} ClickPesa order(s).");
})->purpose('Sync pending ClickPesa order statuses from the ClickPesa API');
