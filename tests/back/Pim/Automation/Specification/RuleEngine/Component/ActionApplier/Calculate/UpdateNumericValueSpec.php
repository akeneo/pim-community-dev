<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\UpdateNumericValue;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UpdateNumericValueSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        NormalizerInterface $normalizer
    ) {
        $normalizer->normalize(Argument::type(ProductPriceInterface::class), 'standard')
            ->will(function (array $arguments): array {
                $price = $arguments[0];
                return [
                    'amount' => $price->getData(),
                    'currency' => $price->getCurrency(),
                ];
            });

        $this->beConstructedWith($attributeRepository, $entityWithValuesBuilder, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UpdateNumericValue::class);
    }

    function it_updates_a_number_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);

        $product = new Product();
        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            null,
            null,
            3.14
        )->shouldBeCalled();

        $this->forEntity($product, ProductTarget::fromNormalized(['field' => 'number']), 3.14);
    }

    function it_updates_a_metric_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::METRIC);
        $attribute->getDefaultMetricUnit()->willReturn('KILOGRAM');
        $attributeRepository->findOneByIdentifier('weight')->willReturn($attribute);

        $product = new Product();
        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            null,
            'ecommerce',
            [
                'amount' => 0.515,
                'unit' => 'GRAM',
            ]
        )->shouldBeCalled();

        $this->forEntity(
            $product,
            ProductTarget::fromNormalized(['field' => 'weight', 'scope' => 'ecommerce', 'unit' => 'GRAM']),
            0.515
        );
    }

    function it_creates_a_new_price_collection_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $product = new Product();
        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            null,
            null,
            [['amount' => 99.90, 'currency' => 'EUR']]
        )->shouldBeCalled();

        $this->forEntity($product, ProductTarget::fromNormalized(['field' => 'price', 'currency' => 'EUR']), 99.90);
    }

    function it_adds_a_new_price_to_an_existing_price_collection_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $product = new Product();
        $product->setValues(new WriteValueCollection([
            PriceCollectionValue::value('price', new PriceCollection([
                new ProductPrice(119.99, 'USD'),
            ]))
        ]));
        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            null,
            null,
            [
                ['amount' => 99.90, 'currency' => 'EUR'],
                ['amount' => 119.99, 'currency' => 'USD'],
            ]
        )->shouldBeCalled();

        $this->forEntity(
            $product,
            ProductTarget::fromNormalized(['field' => 'price', 'currency' => 'EUR']),
            99.90
        );
    }

    function it_replaces_an_existing_price_in_a_price_collection_value(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn(AttributeTypes::PRICE_COLLECTION);
        $attributeRepository->findOneByIdentifier('price')->willReturn($attribute);

        $product = new Product();
        $product->setValues(new WriteValueCollection([
            PriceCollectionValue::value('price', new PriceCollection([
                new ProductPrice(119.99, 'USD'),
                new ProductPrice(109.99, 'EUR'),
            ]))
        ]));
        $entityWithValuesBuilder->addOrReplaceValue(
            $product,
            $attribute,
            null,
            null,
            [
                ['amount' => 99.90, 'currency' => 'EUR'],
                ['amount' => 119.99, 'currency' => 'USD'],
            ]
        )->shouldBeCalled();

        $this->forEntity(
            $product,
            ProductTarget::fromNormalized(['field' => 'price', 'currency' => 'EUR']),
            99.90
        );
    }
}
