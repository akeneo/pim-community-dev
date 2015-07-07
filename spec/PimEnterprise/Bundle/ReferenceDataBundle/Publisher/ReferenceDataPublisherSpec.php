<?php

namespace spec\PimEnterprise\Bundle\ReferenceDataBundle\Publisher;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

class ReferenceDataPublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_reference_data(ReferenceDataInterface $referenceData)
    {
        $this->supports($referenceData)->shouldBe(true);
    }

    function it_publishes_reference_data(ReferenceDataInterface $referenceData)
    {
        $this->publish($referenceData)->shouldReturnAnInstanceOf('Pim\Component\ReferenceData\Model\ReferenceDataInterface');
    }
}
