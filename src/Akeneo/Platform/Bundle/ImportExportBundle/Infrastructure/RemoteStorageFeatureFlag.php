<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;

class RemoteStorageFeatureFlag implements FeatureFlag
{
    public function __construct(
        private FeatureFlags $featureFlags
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->featureFlags->isEnabled('job_automation_remote_storage');
    }
}
