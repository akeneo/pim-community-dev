<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Pim\Bundle\TransformBundle\Denormalizer\Flat\ProductValuesDenormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductValuesDenormalizerSpec extends ObjectBehavior
{
    const FORMAT_CSV  = 'csv';
    const VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Model\ProductValue';

    function let(DenormalizerInterface $valueDenormalizer, FieldNameBuilder $fieldNameBuilder)
    {
        $this->beConstructedWith($valueDenormalizer, $fieldNameBuilder, self::VALUE_CLASS);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_in_csv_of_a_collection_of_variant_group_values()
    {
        $this->supportsDenormalization(
            [],
            ProductValuesDenormalizer::PRODUCT_VALUES_TYPE,
            self::FORMAT_CSV
        )->shouldBe(true);

        $this->supportsDenormalization(
            [],
            'other_type',
            self::FORMAT_CSV
        )->shouldBe(false);
    }

    function it_denormalizes_variant_group_values($fieldNameBuilder, $valueDenormalizer, AttributeInterface $description)
    {
        $fieldNameBuilder->extractAttributeFieldNameInfos('description-ecommerce-en_US')
            ->willReturn(['attribute' => $description, 'locale_code' => 'en_US', 'scope_code' => 'ecommerce'])
            ->shouldBeCalled();
        $fieldNameBuilder->extractAttributeFieldNameInfos('description-ecommerce-fr_FR')
            ->willReturn(['attribute' => $description, 'locale_code' => 'fr_FR', 'scope_code' => 'ecommerce'])
            ->shouldBeCalled();

        $description->getCode()->willReturn('description');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(true);

        $valueDenormalizer->denormalize(
            'My en_US desc',
            self::VALUE_CLASS,
            self::FORMAT_CSV,
            Argument::any()
        )->shouldBeCalled();

        $valueDenormalizer->denormalize(
            'My fr_FR desc',
            self::VALUE_CLASS,
            self::FORMAT_CSV,
            Argument::any()
        )->shouldBeCalled();

        $csvData = [
            'description-ecommerce-en_US' => 'My en_US desc',
            'description-ecommerce-fr_FR' => 'My fr_FR desc',
        ];
        $this->denormalize($csvData, 'ProductValue[]', 'csv');
    }
}
