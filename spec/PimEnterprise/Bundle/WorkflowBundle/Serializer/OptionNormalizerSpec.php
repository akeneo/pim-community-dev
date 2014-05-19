<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;

class OptionNormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_option_in_the_proposal_format(AttributeOption $option)
    {
        $this->supportsNormalization($option, 'proposal')->shouldBe(true);
    }

    function it_normalizes_option_using_its_code(AttributeOption $option)
    {
        $option->getId()->willReturn(12);

        $this->normalize($option, 'proposal')->shouldReturn(12);
    }

    function it_supports_denormalization_of_option_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_simpleselect', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_the_option_code_into_an_attribute_option_entity(
        $repository,
        AttributeOption $option
    ) {
        $repository->find(1)->willReturn($option);

        $this->denormalize(1, 'pim_catalog_simpleselect', 'proposal')->shouldReturn($option);;
    }
}
