<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\Configuration;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * This fake implementation is mainly useful for testing purpose.
 * By default, value is at false on purpose: it's encouraged to explicitely indicate when a feature is activated (whitelist).
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FakeFeatureFlag implements FeatureFlag
{
    public function __construct(
        private bool $enabled = false,
    ) {
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(?string $feature = null): bool
    {
        return $this->enabled;
    }
}
