<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\Context;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\InMemoryFeatureFlags;
use Behat\Behat\Context\Context;

class FeatureFlagContext implements Context
{
    public function __construct(
        private InMemoryFeatureFlags $featureFlags
    ) {
    }

    /**
     *
     * @BeforeScenario
     */
    public function enableReferenceEntityFeatureFlag()
    {
        $this->featureFlags->enable('reference_entity');
    }
}
