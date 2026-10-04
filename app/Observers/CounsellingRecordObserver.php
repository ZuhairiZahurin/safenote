<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\CounsellingRecord;

class CounsellingRecordObserver
{
    /**
     * Handle the CounsellingRecord "created" event.
     */
    public function created(CounsellingRecord $counsellingRecord): void
    {
        AuditLog::record(
            'record_created',
            "Created {$counsellingRecord->category} record for student #{$counsellingRecord->student_id}"
        );
    }

    /**
     * Handle the CounsellingRecord "updated" event.
     */
    public function updated(CounsellingRecord $counsellingRecord): void
    {
        AuditLog::record(
            'record_updated',
            "Updated record #{$counsellingRecord->id} for student #{$counsellingRecord->student_id}"
        );
    }

    /**
     * Handle the CounsellingRecord "deleted" event.
     */
    public function deleted(CounsellingRecord $counsellingRecord): void
    {
        AuditLog::record(
            'record_deleted',
            "Deleted record #{$counsellingRecord->id} for student #{$counsellingRecord->student_id}"
        );
    }
}
