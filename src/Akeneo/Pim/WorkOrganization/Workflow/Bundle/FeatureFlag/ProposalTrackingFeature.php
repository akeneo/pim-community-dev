<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\FeatureFlag;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Application\FeatureFlag;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ProposalTrackingFeature implements FeatureFlag
{
    private $activationFlag;

    public function __construct(bool $activationFlag)
    {
        $this->activationFlag = $activationFlag;
    }

    public function isEnabled(): bool
    {
        return (true === $this->activationFlag);
    }
}
