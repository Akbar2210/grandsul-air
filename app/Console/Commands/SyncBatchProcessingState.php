<?php

namespace App\Console\Commands;

use App\Models\BacaanMeter;
use App\Models\DigitalSignature;
use App\Models\Tagihan;
use App\Models\UnitHunian;
use App\Services\BatchProcessing\BatchTagihanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBatchProcessingState extends Command
{
    protected $signature = 'app:sync-batch-state {--process : Eksekusi batch langsung jika diinginkan}';
    protected $description = 'Sinkronisasi antrean batch tagihan agar data meteran valid berada di antrean batch (menunggu proses massal)';

    public function handle(BatchTagihanService $batchTagihanService)
    {
        if ($this->option('process')) {
            $this->info('Mengeksekusi batch tagihan massal...');
            $result = $batchTagihanService->executeBatchBilling();
            $this->info("Batch berhasil: {$result['new_bills_count']} tagihan baru diterbitkan, {$result['nota_issued_count']} nota ber-RSA disahkan.");
            return 0;
        }

        $this->info('Mengatur antrean batch: Menghapus tagihan prematur agar pembacaan meter valid masuk ke antrean batch.');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DigitalSignature::truncate();
        Tagihan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $totalUnits = UnitHunian::count();
        $validUnbilled = BacaanMeter::where('status_verifikasi', 'valid')->whereDoesntHave('tagihan')->count();
        $terflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
        $pct = round(($validUnbilled / max(1, $totalUnits)) * 100);

        $this->info("Total Unit Hunian : {$totalUnits}");
        $this->info("Data Valid Antrean : {$validUnbilled} unit ({$pct}%)");
        $this->info("Data Ter-flag      : {$terflagCount} unit (perlu review admin)");
        $this->info("Total Tagihan Saat Ini: 0 (Menunggu klik 'Proses Tagihan (Batch)' oleh Admin)");

        return 0;
    }
}

