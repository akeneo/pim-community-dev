<?php

namespace spec\Pim\Component\Connector\ArrayConverter\Flat\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationFieldResolver;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAttributeFieldExtractor;

class ConvertToStructuredFieldSpec extends ObjectBehavior
{
    function let(
        ProductAttributeFieldExtractor $fieldExtractor,
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface  $currencyRepository,
        ProductAssociationFieldResolver $assocFieldResolver,
        AttributeValuesResolver $valuesResolver
    ) {
        $this->beConstructedWith(
            $fieldExtractor,
            $attributeRepository,
            $currencyRepository,
            $assocFieldResolver,
            $valuesResolver
        );
    }

    function convert()
    {
//        $this->convert($item);
    }
}
