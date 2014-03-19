<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\Completeness;
use Pim\Bundle\CatalogBundle\MongoDB\Normalizer\ProductNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_normalization_in_mongodb_json_of_product(ProductInterface $product)
    {
        $this->supportsNormalization($product, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($product, 'json')->shouldBe(false);
        $this->supportsNormalization($product, 'xml')->shouldBe(false);
    }

    function it_normalizes_product(
        SerializerInterface $serializer,
        ProductInterface $product,
        Family $family,
        Completeness $completeness
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);

        $product->getFamily()->willReturn($family);
        $product->getValues()->willReturn([]);
        $product->getCompletenesses()->willReturn([$completeness]);
        $product->getCreated()->willReturn('12');
        $product->getUpdated()->willReturn('123');

        $serializer->normalize($family, 'mongodb_json', [])->willReturn('family normalization');
        $serializer->normalize('12', 'mongodb_json', [])->willReturn('12');
        $serializer->normalize('123', 'mongodb_json', [])->willReturn('12');
        $serializer->normalize($completeness, 'mongodb_json', [])->willReturn(array('completenessCode' => 'completeness normalization'));

        $this->normalize($product, 'mongodb_json', [])->shouldReturn([
            ProductNormalizer::FAMILY_FIELD => 'family normalization',
            ProductNormalizer::COMPLETENESSES_FIELD => array('completenessCode' => 'completeness normalization')
            ProductNormalizer::CREATED_FIELD => '12',
            ProductNormalizer::UPDATED_FIELD => '123'
        ]);
    }

    function it_cannot_normalize_when_injected_serializer_is_not_a_normalizer(
        SerializerInterface $serializer,
        ProductInterface $product
    ) {
        $this->setSerializer($serializer);

        $this->shouldThrow('\LogicException')->duringNormalize($product, 'mongodb_json', []);
    }
}
