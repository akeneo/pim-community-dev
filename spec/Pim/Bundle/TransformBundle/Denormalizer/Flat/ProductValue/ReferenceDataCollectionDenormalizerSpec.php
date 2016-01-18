<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReferenceDataCollectionDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $refDataDenormalizer)
    {
        $this->beConstructedWith(['pim_reference_data_multiselect'], $refDataDenormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\AbstractValueDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_reference_data_collection_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_reference_data_multiselect', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_multiselect', 'csv')->shouldReturn(true);
    }

    function it_returns_an_array_collection_if_data_is_empty()
    {
        $this->denormalize('', 'pim_reference_data_multiselect', 'csv')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->denormalize(null, 'pim_reference_data_multiselect', 'csv')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->denormalize([], 'pim_reference_data_multiselect', 'csv')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_throws_an_exception_if_context_value_is_not_a_product_value_inteface()
    {
        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    'battlecruiser',
                    'pim_reference_data_simpleselect',
                    'csv',
                    ['value' => 'not_a_product_value']
                ]
            );
    }

    function it_throws_an_exception_if_there_is_no_attribute_in_context(ProductValueInterface $productValue)
    {
        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    'battlecruiser,destroyer',
                    'pim_reference_data_multiselect',
                    'csv',
                    ['value' => $productValue]
                ]
            );
    }

    function it_denormalizes_a_collection_of_reference_data_values(
        $refDataDenormalizer,
        AttributeInterface $attribute,
        ReferenceDataInterface $refData1,
        ReferenceDataInterface $refData2,
        ProductValueInterface $productValue
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $context = ['value' => $productValue];

        $refDataDenormalizer->denormalize(
            'battlecruiser',
            'pim_reference_data_multiselect',
            null,
            $context
        )
        ->shouldBeCalled()
        ->willReturn($refData1);

        $refDataDenormalizer->denormalize(
            'destroyer',
            'pim_reference_data_multiselect',
            null,
            $context
        )
        ->shouldBeCalled()
        ->willReturn($refData2);

        $this->denormalize('battlecruiser,destroyer', 'pim_reference_data_multiselect', null, $context)
            ->shouldHaveCount(2);
    }
}
