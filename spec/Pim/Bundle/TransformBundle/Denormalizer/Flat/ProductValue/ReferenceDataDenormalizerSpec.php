<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Repository\ReferenceDataRepository;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\Repository\ReferenceDataRepositoryResolverInterface;

class ReferenceDataDenormalizerSpec extends ObjectBehavior
{
    function let(ReferenceDataRepositoryResolverInterface $resolver)
    {
        $this->beConstructedWith(['pim_reference_data_simpleselect'], $resolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValue\AbstractValueDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_reference_data_values_from_json()
    {
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_catalog_text', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_multiselect', 'csv')->shouldReturn(false);
        $this->supportsDenormalization([], 'pim_reference_data_simpleselect', 'csv')->shouldReturn(true);
    }

    function it_returns_null_if_data_is_empty()
    {
        $this->denormalize('', 'pim_reference_data_simpleselect', 'csv')->shouldReturn(null);
        $this->denormalize(null, 'pim_reference_data_simpleselect', 'csv')->shouldReturn(null);
        $this->denormalize([], 'pim_reference_data_simpleselect', 'csv')->shouldReturn(null);
    }

    function it_throws_an_exception_if_context_value_is_not_a_product_value_interface()
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
                    'battlecruiser',
                    'pim_reference_data_simpleselect',
                    'json',
                    ['value' => $productValue]
                ]
            );
    }

    function it_denormalizes_data_into_reference_data(
        $resolver,
        AttributeInterface $attribute,
        ReferenceDataInterface $battlecruiser,
        ReferenceDataRepository $referenceDataRepo,
        ProductValueInterface $productValue
    ) {
        $attribute->getReferenceDataName()->willReturn('starship');
        $productValue->getAttribute()->willReturn($attribute);
        $resolver->resolve('starship')->willReturn($referenceDataRepo);
        $referenceDataRepo->findOneBy(['code' => 'battlecruiser'])->willReturn($battlecruiser);

        $this
            ->denormalize(
                'battlecruiser',
                'pim_reference_data_simpleselect',
                'csv',
                ['value' => $productValue]
            )
            ->shouldReturn($battlecruiser);
    }
}
