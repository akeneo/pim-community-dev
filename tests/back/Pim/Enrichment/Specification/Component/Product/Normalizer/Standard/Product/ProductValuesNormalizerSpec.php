<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValuesNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValuesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_collection_values()
    {
        $realValue = ScalarValue::value('attribute', null);

        $valuesCollection = new WriteValueCollection([$realValue]);
        $valuesArray = [$realValue];
        $emptyValuesCollection = new WriteValueCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];

        $this->supportsNormalization($valuesCollection, 'standard')->shouldReturn(true);
        $this->supportsNormalization($valuesArray, 'standard')->shouldReturn(false);
        $this->supportsNormalization($emptyValuesCollection, 'standard')->shouldReturn(true);
        $this->supportsNormalization($randomCollection, 'standard')->shouldReturn(false);
        $this->supportsNormalization($randomArray, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($valuesCollection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values_in_standard_format(
        NormalizerInterface $normalizer,
        ValueInterface $textValue,
        ValueInterface $priceValue,
        WriteValueCollection $values,
        \ArrayIterator $valuesIterator
    ) {
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->current()->willReturn($textValue, $priceValue);
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttributeCode()->willReturn('text');
        $priceValue->getAttributeCode()->willReturn('price');

        $normalizer
            ->normalize($textValue, 'standard', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => null, 'scope' => null, 'value' => 'foo']);

        $normalizer
            ->normalize($priceValue, 'standard', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => [
                ['amount' => '12.50', 'currency' => 'USD'],
                ['amount' => '15.00', 'currency' => 'EUR']
            ]]);

        $this
            ->normalize($values, 'standard')
            ->shouldReturn(
                [
                    'text' => [
                        ['locale' => null, 'scope' => null, 'value' => 'foo']
                    ],
                    'price' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => [
                            ['amount' => '12.50', 'currency' => 'USD'],
                            ['amount' => '15.00', 'currency' => 'EUR']
                        ]]
                    ]
                ]
            );
    }
}
