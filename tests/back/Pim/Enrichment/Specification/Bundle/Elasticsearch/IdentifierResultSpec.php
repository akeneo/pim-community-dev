<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PhpSpec\ObjectBehavior;
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

    function it_cannot_instanciate_for_a_product_model_without_identifier()
    {
        $this->beConstructedWith(null, ProductModelInterface::class, 'product_model_foo');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_instanciate_for_a_product_without_identifier()
    {
        $uuidAsString = 'product_' . Uuid::uuid4()->toString();
        $this->beConstructedWith(null, ProductInterface::class, $uuidAsString);
        $this->getId()->shouldReturn($uuidAsString);
        $this->getIdentifier()->shouldReturn(null);
    }
}
