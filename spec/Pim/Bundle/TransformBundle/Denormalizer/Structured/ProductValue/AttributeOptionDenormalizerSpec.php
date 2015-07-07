<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;

class AttributeOptionDenormalizerSpec extends ObjectBehavior
{
    function let(AttributeOptionRepositoryInterface $repository)
    {
        $this->beConstructedWith(
            ['pim_catalog_simpleselect'],
            $repository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\AttributeOptionDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_attribute_option_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_catalog_simpleselect', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'foo', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_simpleselect', 'csv')->shouldReturn(false);
    }

    function it_returns_the_requested_attribute_option($repository, AttributeInterface $color, AttributeOptionInterface $red)
    {
        $color->getCode()->willReturn('color');

        $repository
            ->findOneByIdentifier('color.red')
            ->shouldBeCalled()
            ->willReturn($red);

        $this
            ->denormalize('red', 'pim_catalog_simpleselect', null, ['attribute' => $color])
            ->shouldReturn($red);
    }

    function it_returns_null_if_the_data_is_empty()
    {
        $this->denormalize('', 'pim_catalog_simpleselect')->shouldReturn(null);
        $this->denormalize(null, 'pim_catalog_simpleselect')->shouldReturn(null);
    }
}
