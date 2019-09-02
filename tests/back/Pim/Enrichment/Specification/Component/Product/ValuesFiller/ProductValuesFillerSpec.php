<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Prophecy\Argument;

class ProductValuesFillerSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ValueFactory $valueFactory
    ) {
        $this->beConstructedWith(
            $entityWithValuesBuilder,
            $valuesResolver,
            $currencyRepository,
            $attributeRepository,
            $valueFactory
        );
    }

    function it_fills_missing_product_values_from_family_on_new_product(
        AttributeValuesResolverInterface $valuesResolver,
        FamilyInterface $family,
        ProductInterface $product,
        AttributeInterface $sku,
        AttributeInterface $name,
        AttributeInterface $desc,
        ValueInterface $skuValue,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ValueFactory $valueFactory,
        ValueInterface $emptyValue
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('sku')->willReturn($sku);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $desc->getCode()->willReturn('description');
        $desc->getType()->willReturn('pim_catalog_text');
        $desc->isLocalizable()->willReturn(true);
        $desc->isScopable()->willReturn(true);

        // get expected attributes
        $product->getUsedAttributeCodes()->willReturn(['sku']);
        $family->getAttributes()->willReturn([$sku, $name, $desc]);
        $product->getFamily()->willReturn($family);

        // get eligible values
        $valuesResolver->resolveEligibleValues(['sku' => $sku, 'name' => $name, 'description' => $desc], null, null)
            ->willReturn([
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'ecommerce'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => 'print'
                ],
                [
                    'attribute' => 'description',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => 'print'
                ]
            ]);

        // get existing values
        $skuValue->getAttributeCode()->willReturn('sku');
        $skuValue->getLocaleCode()->willReturn(null);
        $skuValue->getScopeCode()->willReturn(null);
        $product->getValues()->willReturn([$skuValue]);

        $valueFactory->createNull(Argument::cetera())->shouldBeCalledTimes(6)->willReturn($emptyValue);
        $product->addValue(Argument::cetera())->shouldBeCalledTimes(6);

        $this->fillMissingValues($product);
    }
}
