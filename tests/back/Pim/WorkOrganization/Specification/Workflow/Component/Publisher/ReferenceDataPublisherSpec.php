<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

class ReferenceDataPublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf(PublisherInterface::class);
    }

    function it_supports_reference_data(ReferenceDataInterface $referenceData)
    {
        $this->supports($referenceData)->shouldBe(true);
    }

    function it_publishes_reference_data(ReferenceDataInterface $referenceData)
    {
        $this->publish($referenceData)->shouldReturnAnInstanceOf(ReferenceDataInterface::class);
    }
}
