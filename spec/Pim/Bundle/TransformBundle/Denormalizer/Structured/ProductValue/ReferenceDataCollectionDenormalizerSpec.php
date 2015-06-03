<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue\AbstractValueDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_reference_data_collection_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_reference_data_multiselect', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_multiselect', 'csv')->shouldReturn(false);
    }

    function it_returns_an_array_collection_if_data_is_empty()
    {
        $this->denormalize('', 'pim_reference_data_multiselect', 'json')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->denormalize(null, 'pim_reference_data_multiselect', 'json')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
        $this->denormalize([], 'pim_reference_data_multiselect', 'json')
            ->shouldBeAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_throws_an_exception_if_data_is_not_an_array(AttributeInterface $attribute)
    {
        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    42,
                    'pim_reference_data_multiselect',
                    null,
                    ['attribute' => $attribute]
                ]
            );

        $this->shouldThrow('Symfony\Component\Routing\Exception\InvalidParameterException')
            ->during(
                'denormalize',
                [
                    'this is a string',
                    'pim_reference_data_multiselect',
                    null,
                    ['attribute' => $attribute]
                ]
            );
    }

    function it_denormalizes_a_collection_of_reference_data_values(
        $refDataDenormalizer,
        AttributeInterface $attribute,
        ReferenceDataInterface $refData1,
        ReferenceDataInterface $refData2
    ) {
        $data = [
            ['code' => 'first_ref'],
            ['code' => 'second_ref']
        ];

        $context = ['attribute' => $attribute];

        $refDataDenormalizer->denormalize(
            ['code' => 'first_ref'],
            'pim_reference_data_multiselect',
            null,
            $context
        )
        ->shouldBeCalled()
        ->willReturn($refData1);

        $refDataDenormalizer->denormalize(
            ['code' => 'second_ref'],
            'pim_reference_data_multiselect',
            null,
            $context
        )
        ->shouldBeCalled()
        ->willReturn($refData2);

        $this->denormalize($data, 'pim_reference_data_multiselect', null, $context)
            ->shouldHaveCount(2);
    }
}
