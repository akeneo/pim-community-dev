<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\FeatureFlag;

use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ProposalTrackingFeatureSpec extends ObjectBehavior
{
    public function it_validates_that_tracking_of_proposals_is_enabled()
    {
        $this->beConstructedWith(true);

        $this->isEnabled()->shouldBe(true);
    }
    public function it_validates_that_tracking_of_proposals_is_disabled()
    {
        $this->beConstructedWith(false);

        $this->isEnabled()->shouldBe(false);
    }
}
