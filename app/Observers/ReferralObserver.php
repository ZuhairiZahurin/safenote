<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Referral;

class ReferralObserver
{
    public function created(Referral $referral): void
    {
        AuditLog::record(
            'referral_created',
            "Referred student #{$referral->student_id} ({$referral->issue_type}, {$referral->urgency} urgency)"
        );
    }

    public function updated(Referral $referral): void
    {
        if ($referral->wasChanged('status')) {
            AuditLog::record(
                'referral_status_changed',
                "Referral #{$referral->id} moved to {$referral->status}"
            );
        }
    }

    public function deleted(Referral $referral): void
    {
        AuditLog::record('referral_deleted', "Deleted referral #{$referral->id}");
    }
}
