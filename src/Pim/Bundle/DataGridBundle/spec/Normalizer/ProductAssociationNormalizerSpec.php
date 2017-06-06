<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyTranslationInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\SerializerInterface;

class ProductAssociationNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\ProductAssociationNormalizer');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
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
        ProductInterface $product,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ProductInterface $currentProduct
    ) {
        $context = [
            'locales'             => ['en_US'],
            'channels'            => ['ecommerce'],
            'current_product'     => $currentProduct,
            'association_type_id' => 1,
            'is_associated'       => false,
        ];

        $currentProduct->getAssociations()->willReturn([]);

        $product->getFamily()->willReturn($family);
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
        $product->getLabel('en_US')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $completeness->getLocale()->willReturn($localeEN);
        $completeness->getChannel()->willReturn($channelEcommerce);
        $completeness->getRatio()->willReturn(76);

        $localeEN->getCode()->willReturn('en_US');
        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier'    => 'purple_tshirt',
            'family'        => 'Tshirt',
            'enabled'       => true,
            'created'       => '2017-01-01T01:03:34+01:00',
            'updated'       => '2017-01-01T01:04:34+01:00',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'Purple tshirt',
            'completeness'  => 76
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }
    function it_normalizes_a_product_without_label(
        $serializer,
        ProductInterface $product,
        FamilyInterface $family,
        FamilyTranslationInterface $familyEN,
        Completeness $completeness,
        LocaleInterface $localeEN,
        ChannelInterface $channelEcommerce,
        ProductInterface $currentProduct
    ) {
        $context = [
            'locales'             => ['en_US'],
            'channels'            => ['ecommerce'],
            'current_product'     => $currentProduct,
            'association_type_id' => 1,
            'is_associated'       => false,
        ];

        $currentProduct->getAssociations()->willReturn([]);

        $product->getFamily()->willReturn($family);
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
        $product->getLabel('en_US')->willReturn('Purple tshirt');
        $product->getCompletenesses()->willReturn([$completeness]);
        $completeness->getLocale()->willReturn($localeEN);
        $completeness->getChannel()->willReturn($channelEcommerce);
        $completeness->getRatio()->willReturn(76);

        $localeEN->getCode()->willReturn('en_US');
        $channelEcommerce->getCode()->willReturn('ecommerce');

        $data = [
            'identifier'    => 'purple_tshirt',
            'family'        => '[tshirt]',
            'enabled'       => true,
            'created'       => '2017-01-01T01:03:34+01:00',
            'updated'       => '2017-01-01T01:04:34+01:00',
            'is_checked'    => false,
            'is_associated' => false,
            'label'         => 'Purple tshirt',
            'completeness'  => 76
        ];

        $this->normalize($product, 'datagrid', $context)->shouldReturn($data);
    }
}
