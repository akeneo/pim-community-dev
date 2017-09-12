<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Standard\ProductModelNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareInterface;

class ProductModelNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_serializer_aware()
    {
        $this->shouldImplement(SerializerAwareInterface::class);
    }

    function it_normalizes_product_model(
        ProductModelInterface $productModel,
        Serializer $normalizer,
        FamilyVariantInterface $familyVariant,
        ValueCollection $values
    ) {
        $this->setSerializer($normalizer);

        $productModel->getCode()->willReturn('code');
        $productModel->getFamilyVariant()->willReturn($familyVariant);
        $familyVariant->getCode()->willReturn('family_variant');

        $productModel->getValues()->willReturn($values);

        $normalizer
            ->normalize($values, 'standard', [])
            ->willReturn(['name' => [['locale' => null, 'scope' => null, 'value' => 'foo']]]);

        $productModel->getCategoryCodes()->willReturn(['tshirt']);

        $created = new \DateTime('2010-06-23');
        $productModel->getCreated()->willReturn($created);
        $normalizer->normalize($created, 'standard', [])->willReturn('2010-06-23T00:00:00+01:00');

        $updated = new \DateTime('2010-06-23 23:00:00');
        $productModel->getUpdated()->willReturn($updated);
        $normalizer->normalize($updated, 'standard', [])->willReturn('2010-06-23T23:00:00+01:00');

        $this->normalize($productModel, 'standard')->shouldReturn([
            'code' => 'code',
            'family_variant' => 'family_variant',
            'categories' => ['tshirt'],
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'value'  => 'foo',
                    ]
                ]
            ],
            'created' => '2010-06-23T00:00:00+01:00',
            'updated' => '2010-06-23T23:00:00+01:00',
        ]);
    }

    function it_supports_standard_format(ProductModelInterface $productModel)
    {
        $this->supportsNormalization($productModel, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'xml')->shouldReturn(false);
        $this->supportsNormalization($productModel, 'csv')->shouldReturn(false);
    }

    function it_throws_an_exception_if_the_serializer_is_not_set(ProductModelInterface $productModel)
    {
        $this->shouldThrow(\LogicException::class)->during('normalize', [$productModel]);
    }
}
