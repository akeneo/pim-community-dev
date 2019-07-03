<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Oro\Bundle\PimDataGridBundle\Normalizer\Product\ProductValuesNormalizer;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer, PresenterRegistryInterface $presenterRegistry, UserContext $userContext)
    {
        $this->beConstructedWith($presenterRegistry, $userContext);
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');

        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValuesNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
    }

    function it_supports_datagrid_format_and_collection_values()
    {
        $attribute = new Attribute();
        $attribute->setCode('attribute');
        $attribute->setBackendType('text');
        $realValue = ScalarValue::value($attribute, null);

        $valuesCollection = new WriteValueCollection([$realValue]);
        $valuesArray = [$realValue];
        $emptyValuesCollection = new WriteValueCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];

        $this->supportsNormalization($valuesCollection, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($valuesArray, 'datagrid')->shouldReturn(false);
        $this->supportsNormalization($emptyValuesCollection, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($randomCollection, 'datagrid')->shouldReturn(false);
        $this->supportsNormalization($randomArray, 'datagrid')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
        $this->supportsNormalization($valuesCollection, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
    }

    function it_normalizes_collection_of_product_values(
        $serializer,
        $presenterRegistry,
        $userContext,
        ValueInterface $textValue,
        ValueInterface $priceValue,
        WriteValueCollection $values,
        \ArrayIterator $valuesIterator,
        PresenterInterface $pricePresenter
    ) {
        $values->getIterator()->willReturn($valuesIterator);
        $valuesIterator->rewind()->shouldBeCalled();
        $valuesIterator->valid()->willReturn(true, true, false);
        $valuesIterator->current()->willReturn($textValue, $priceValue);
        $valuesIterator->next()->shouldBeCalled();

        $textValue->getAttributeCode()->willReturn('text');
        $priceValue->getAttributeCode()->willReturn('price');

        $serializer
            ->normalize($textValue, 'datagrid', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => null, 'scope' => null, 'data' => 'foo']);

        $prices = [
            ['amount' => '12.50', 'currency' => 'USD'],
            ['amount' => '15.00', 'currency' => 'EUR']
        ];

        $serializer
            ->normalize($priceValue, 'datagrid', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => $prices]);

        $userContext->getUiLocaleCode()->willReturn('en_US');
        $presenterRegistry->getPresenterByAttributeCode('text')->willReturn(null);

        $presenterRegistry->getPresenterByAttributeCode('price')->willReturn($pricePresenter);
        $pricePresenter->present($prices, ['locale' => 'en_US'])->willReturn('$15.00, $12.50');

        $this
            ->normalize($values, 'datagrid')
            ->shouldReturn(
                [
                    'text' => [
                        ['locale' => null, 'scope' => null, 'data' => 'foo']
                    ],
                    'price' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => '$15.00, $12.50']
                    ]
                ]
            );
    }
}
