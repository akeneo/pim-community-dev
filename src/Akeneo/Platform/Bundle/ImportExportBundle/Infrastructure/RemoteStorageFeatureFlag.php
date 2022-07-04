<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

class RemoteStorageFeatureFlag
{
    private const REMOTE_STORAGE_JOB_CODES = [
        'xlsx_product_import',
        'xlsx_product_export',
        'xlsx_tailored_product_export',
        'xlsx_tailored_product_import',
    ];

    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function isEnabled(string $jobName): bool
    {
        return $this->featureFlags->isEnabled('job_automation_remote_storage')
            && in_array($jobName, self::REMOTE_STORAGE_JOB_CODES);
    }
}
