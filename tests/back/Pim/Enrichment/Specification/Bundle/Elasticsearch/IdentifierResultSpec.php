<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Ramsey\Uuid\Uuid;

class IdentifierResultSpec extends ObjectBehavior
{
    function it_gets_the_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->getIdentifier()->shouldReturn('foo');
    }

    function it_gets_the_type()
    {
        $this->beConstructedWith('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->getType()->shouldReturn(ProductInterface::class);
    }

    function it_gets_the_id()
    {
        $uuidAsString = 'product_' . Uuid::uuid4()->toString();
        $this->beConstructedWith('foo', ProductInterface::class, $uuidAsString);
        $this->getId()->shouldReturn($uuidAsString);
    }

    function it_determines_if_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->isProductIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductModelInterface::class, 'product_model_foo');
        $this->isProductIdentifierEquals('foo')->shouldReturn(false);
    }

    function it_determines_if_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductModelInterface::class, 'product_model_foo');
        $this->isProductModelIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->isProductModelIdentifierEquals('foo')->shouldReturn(false);
    }
}
