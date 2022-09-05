<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Ramsey\Uuid\Uuid;

class IdentifierResultsSpec extends ObjectBehavior
{
    function it_adds_a_result_identifier()
    {
        $uuidAsString = 'product_' .Uuid::uuid4()->toString();
        $this->add('foo', ProductInterface::class, $uuidAsString);

        $all = $this->all();
        $all->shouldHaveCount(1);
        $all->shouldBeArray();
        $all[0]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[0]->getIdentifier()->shouldReturn('foo');
        $all[0]->getType()->shouldReturn(ProductInterface::class);
        $all[0]->getId()->shouldReturn($uuidAsString);
    }

    function it_checks_if_empty()
    {
        $this->isEmpty()->shouldReturn(true);
    }

    function it_checks_if_not_empty()
    {
        $uuidAsString = 'product_' .Uuid::uuid4()->toString();
        $this->add('foo', ProductInterface::class, $uuidAsString);
        $this->isEmpty()->shouldReturn(false);
    }

    function it_returns_all_elements_when_empty()
    {
        $this->all()->shouldReturn([]);
    }

    function it_returns_all_elements()
    {
        $this->add('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->add('bar', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->add('baz', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());

        $all = $this->all();
        $all->shouldHaveCount(3);
        $all->shouldBeArray();
        $all[0]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[1]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[2]->shouldBeAnInstanceOf(IdentifierResult::class);
    }

    function it_returns_product_identifiers()
    {
        $this->add('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->add('bar', ProductModelInterface::class, 'product_model_bar');
        $this->add('baz', ProductModelInterface::class, 'product_model_baz');
        $this->add('qux', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());

        $this->getProductIdentifiers()->shouldReturn(['foo', 'qux']);
    }

    function it_returns_product_model_identifiers()
    {
        $this->add('foo', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());
        $this->add('bar', ProductModelInterface::class, 'product_model_bar');
        $this->add('baz', ProductModelInterface::class, 'product_model_bar');
        $this->add('qux', ProductInterface::class, 'product_' . Uuid::uuid4()->toString());

        $this->getProductModelIdentifiers()->shouldReturn(['bar', 'baz']);
    }
}
