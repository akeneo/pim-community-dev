<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductAssociationNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductAssociationNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        ImageNormalizer $imageNormalizer,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->beConstructedWith($imageNormalizer, $getProductCompletenesses);

        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAssociationNormalizer::class);
        $this->shouldBeAnInstanceOf(SerializerAwareInterface::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_datagrid_format_and_product_value(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($product, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_product_with_label(
        $serializer,
        $imageNormalizer,
        $getProductCompletenesses,
        ProductInterface $product,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ProductInterface $currentProduct,
        ValueInterface $image
    ) {
        $context = [
            'locales'             => ['en_US'],
            'data_locale'         => 'en_US',
            'channels'            => ['ecommerce'],
            'current_product'     => $currentProduct,
            'association_type_id' => 1,
            'is_associated'       => false,
        ];

        $currentProduct->getAssociations()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $product->getId()->willReturn(42);

        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn('Tshirt');

        $product->getIdentifier()->willReturn('purple_tshirt');
        $product->isEnabled()->willReturn(true);
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US', 'ecommerce')->willReturn('Purple tshirt');

        $getProductCompletenesses->fromProductId(42)->willReturn(new ProductCompletenessCollection(42, [
            new ProductCompleteness('ecommerce', 'en_US', 10, 1)
        ]));

        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath' => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $data = [
            'identifier'    => 'purple_tshirt',
            'family'        => 'Tshirt',
            'enabled'       => true,
            'created'       => '2017-01-01T01:03:34+01:00',
            'updated'       => '2017-01-01T01:04:34+01:00',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'Purple tshirt',
            'completeness'  => 90,
            'image'         => [
                'filePath' => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ]
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }

    function it_normalizes_a_product_without_label(
        $serializer,
        $imageNormalizer,
        $getProductCompletenesses,
        ProductInterface $product,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        ProductInterface $currentProduct,
        ValueInterface $image
    ) {
        $context = [
            'locales'             => ['en_US'],
            'data_locale'         => 'en_US',
            'channels'            => ['ecommerce'],
            'current_product'     => $currentProduct,
            'association_type_id' => 1,
            'is_associated'       => false,
        ];

        $currentProduct->getAssociations()->willReturn([]);

        $product->getFamily()->willReturn($family);
        $product->getId()->willReturn(42);
        $family->getCode()->willReturn('tshirt');
        $family->getTranslation('en_US')->willReturn($familyEN);
        $familyEN->getLabel()->willReturn(null);

        $product->getIdentifier()->willReturn('purple_tshirt');
        $product->isEnabled()->willReturn(true);
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->getCreated()->willReturn($created);
        $serializer->normalize($created, 'datagrid', $context)->willReturn('2017-01-01T01:03:34+01:00');

        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->getUpdated()->willReturn($updated);
        $serializer->normalize($updated, 'datagrid', $context)->willReturn('2017-01-01T01:04:34+01:00');
        $product->getLabel('en_US', 'ecommerce')->willReturn('Purple tshirt');

        $getProductCompletenesses->fromProductId(42)->willReturn(new ProductCompletenessCollection(42, [
            new ProductCompleteness('ecommerce', 'en_US', 10, 1)
        ]));

        $product->getImage()->willReturn($image);
        $imageNormalizer->normalize($image, Argument::any())->willReturn([
            'filePath' => '/p/i/m/4/all.png',
            'originalFileName' => 'all.png',
        ]);

        $data = [
            'identifier'    => 'purple_tshirt',
            'family'        => '[tshirt]',
            'enabled'       => true,
            'created'       => '2017-01-01T01:03:34+01:00',
            'updated'       => '2017-01-01T01:04:34+01:00',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'Purple tshirt',
            'completeness'  => 90,
            'image'         => [
                'filePath' => '/p/i/m/4/all.png',
                'originalFileName' => 'all.png',
            ]
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }
}
