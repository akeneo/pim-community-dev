<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Infrastructure\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Syndication\Domain\Query\Platform\HasAtLeastOnePlatformInterface;

final class SyndicationFeature implements FeatureFlag
{
    public function __construct(
        private HasAtLeastOnePlatformInterface $hasAtLeastOnePlatform
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->hasAtLeastOnePlatform->execute();
    }
}
