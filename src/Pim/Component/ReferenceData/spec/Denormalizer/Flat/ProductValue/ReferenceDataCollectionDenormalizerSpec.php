<?php

namespace spec\Pim\Component\ReferenceData\Denormalizer\Flat\ProductValue;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

class ReferenceDataCollectionDenormalizerSpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $refDataDenormalizer)
    {
        $this->beConstructedWith(['pim_reference_data_multiselect'], $refDataDenormalizer);
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
        $this->shouldThrow('\InvalidArgumentException')
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
        $this->shouldThrow('\InvalidArgumentException')
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
        ProductValueInterface $productValue,
        ObjectRepository $repository
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getReferenceDataName()->willReturn('reference');
        $context = ['value' => $productValue];

        $refDataDenormalizer->resolve('reference')->willReturn($repository);

        $repository->findOneBy(['code' => 'battlecruiser'])->willReturn($refData1);
        $repository->findOneBy(['code' => 'destroyer'])->willReturn($refData2);

        $this->denormalize(
            'battlecruiser, destroyer',
            'pim_reference_data_multiselect',
            null,
            $context
        )->shouldHaveCount(2);
    }
}
