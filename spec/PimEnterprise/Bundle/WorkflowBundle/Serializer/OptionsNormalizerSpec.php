<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Symfony\Component\Serializer\SerializerInterface;

class OptionsNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_denormalizer_which_is_also_aware_of_the_serializer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer');
    }

    function it_supports_denormalization_of_options_collection_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_multiselect', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_the_options_collection_using_the_serializer(
        $serializer,
        Collection $collection,
        AttributeOption $option1,
        AttributeOption $option2
    ) {
        $serializer->denormalize(1234, 'pim_catalog_simpleselect', 'proposal', [])->willReturn($option1);
        $serializer->denormalize(5678, 'pim_catalog_simpleselect', 'proposal', [])->willReturn($option2);
        $collection->add($option1)->shouldBeCalled();
        $collection->add($option2)->shouldBeCalled();
        $collection->remove(2)->shouldBeCalled();

        $this->denormalize([1234, 5678, null], 'pim_catalog_multiselect', 'proposal', ['instance' => $collection ])->shouldReturn($collection);
    }
}
