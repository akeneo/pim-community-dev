<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\GetOperandValue;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetOperandValueSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureConverter $measureConverter
    ) {
        $this->beConstructedWith($attributeRepository, $measureConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetOperandValue::class);
    }

    function it_returns_null_if_value_does_not_exist()
    {
        $this->fromEntity(new Product(), Operand::fromNormalized(['field' => 'foo']))
            ->shouldReturn(null);
    }

    function it_returns_a_number_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);

        $product = new Product();
        $product->setValues(
            new WriteValueCollection([
                ScalarValue::value('number', 10.0)
            ])
        );

        $this->fromEntity($product, Operand::fromNormalized(['field' => 'number']))
            ->shouldReturn(10.0);
    }

    function it_returns_a_metric_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        MeasureConverter $measureConverter,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::METRIC);
        $attribute->getMetricFamily()->willReturn('LENGTH');
        $attribute->getDefaultMetricUnit()->willReturn('CENTIMETER');
        $attributeRepository->findOneByIdentifier('length')->willReturn($attribute);

        $measureConverter->setFamily('LENGTH')->shouldBeCalled();
        $measureConverter->convert('INCH', 'CENTIMETER', 2.5)->willReturn(6.35);

        $product = new Product();
        $product->setValues(
            new WriteValueCollection(
                [
                    MetricValue::value('length', new Metric('LENGTH', 'INCH', '2.5', 'METER', 0.0635))
                ]
            )
        );

        $this->fromEntity($product, Operand::fromNormalized(['field' => 'length']))
             ->shouldReturn(6.35);
    }

    function it_returns_a_price_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $product = new Product();
        $product->setValues(
            new WriteValueCollection(
                [
                    PriceCollectionValue::value(
                        'price',
                        new PriceCollection(
                            [new ProductPrice(20.00, 'EUR'), new ProductPrice(25.00, 'USD')]
                        )
                    )
                ]
            )
        );

        $this->fromEntity($product, Operand::fromNormalized(['field' => 'price', 'currency' => 'USD']))
             ->shouldReturn(25.0);
    }
}
