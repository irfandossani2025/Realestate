<?php

namespace App\Console\Commands;

use App\Models\Lease;
use Illuminate\Console\Command;

class CheckLeaseRenewals extends Command
{
    protected $signature = 'leases:check-renewals';

    protected $description = 'Mark active leases that have entered their renewal notice period';

    public function handle(): int
    {
        $updated = 0;
        Lease::query()->where('status', 'active')->where('renewal_status', 'not_due')->each(function (Lease $lease) use (&$updated): void {
            if (today()->addDays($lease->renewal_notice_days)->gte($lease->ends_on)) {
                $lease->update(['renewal_status' => 'due_soon']);
                $updated++;
            }
        });
        $this->info("{$updated} lease renewal(s) marked due soon.");

        return self::SUCCESS;
    }
}
