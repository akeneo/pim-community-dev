<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Mock;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FakeFeatureFlag implements FeatureFlag
{
    private bool $enabled;

    public function __construct(bool $default = true)
    {
        $this->enabled = $default;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
