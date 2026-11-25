<?php

namespace App\Jobs;

use App\Services\BatchProcessing\BatchTagihanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Konsentrasi Enterprise / Batch Processing (Wahyu Anwar)
 * Worker Queue Job untuk mengeksekusi Batch Processing di antrean latar belakang.
 */
class ProcessBatchTagihanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $periode = null
    ) {}

    /**
     * Eksekusi batch processing tagihan melalui BatchTagihanService.
     */
    public function handle(BatchTagihanService $batchTagihanService): void
    {
        $batchTagihanService->executeBatchBilling($this->periode);
    }
}

