<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

class IdentifierResultSpec extends ObjectBehavior
{
    function it_gets_the_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class);
        $this->getIdentifier()->shouldReturn('foo');
    }

    function it_gets_the_type()
    {
        $this->beConstructedWith('foo', ProductInterface::class);
        $this->getType()->shouldReturn(ProductInterface::class);
    }

    function it_determines_if_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class);
        $this->isProductIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductModelInterface::class);
        $this->isProductIdentifierEquals('foo')->shouldReturn(false);
    }

    function it_determines_if_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductModelInterface::class);
        $this->isProductModelIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class);
        $this->isProductModelIdentifierEquals('foo')->shouldReturn(false);
    }
}
