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

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProposalTracking;
use PhpSpec\ObjectBehavior;

class ProposalTrackingSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            ProposalTracking::TYPE_PRODUCT,
            42,
            new \DateTime(),
            [
                'source' => 'pim',
                'author_label' => 'Mary Smith',
                'status' => 'approved',
                'attributes' => ['att_1' => 'Name']
            ],
        );
    }

    public function it_gives_the_entity_type()
    {
        $this->getEntityType()->shouldReturn(ProposalTracking::TYPE_PRODUCT);
    }

    public function it_gives_the_entity_id()
    {
        $this->getEntityId()->shouldReturn(42);
    }

    public function it_gives_the_event_date()
    {
        $eventDate = new \DateTime();
        $this->beConstructedWith(ProposalTracking::TYPE_PRODUCT, 42, $eventDate, []);

        $this->getEventDate()->shouldReturn($eventDate);
    }

    public function it_gives_the_payload()
    {
        $this->getPayload()->shouldReturn([
            'source' => 'pim',
            'author_label' => 'Mary Smith',
            'status' => 'approved',
            'attributes' => ['att_1' => 'Name']
        ]);
    }
}
