<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AttributeOptionsDenormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith(['pim_catalog_multiselect']);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\AttributeOptionsDenormalizer');
    }

    function it_is_a_serializer_aware_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_attribute_options_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_multiselect', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'foo', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_multiselect', 'csv')->shouldReturn(false);
    }

    function it_returns_the_requested_attribute_options(
        $serializer,
        AttributeInterface $color,
        AttributeOptionInterface $red,
        AttributeOptionInterface $blue
    ) {
        $color->getCode()->willReturn('color');

        $serializer
            ->denormalize('red', 'pim_catalog_simpleselect', 'json', ['attribute' => $color])
            ->shouldBeCalled()
            ->willReturn($red);

        $serializer
            ->denormalize('blue', 'pim_catalog_simpleselect', 'json', ['attribute' => $color])
            ->shouldBeCalled()
            ->willReturn($blue);

        $options = $this
            ->denormalize(['red', 'blue'], 'pim_catalog_multiselect', 'json', ['attribute' => $color]);

        $options->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
        $options->shouldHaveCount(2);
        $options[0]->shouldBe($red);
        $options[1]->shouldBe($blue);
    }

    function it_returns_null_if_the_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_multiselect')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_multiselect')->shouldReturn(null);
        $this->denormalize([], 'pim_catalog_multiselect')->shouldReturn(null);
    }
}
