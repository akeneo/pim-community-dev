<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

class IdentifierResultsSpec extends ObjectBehavior
{
    function it_adds_a_result_identifier()
    {
        $this->add('foo', ProductDraft::class);

        $all = $this->all();
        $all->shouldHaveCount(1);
        $all->shouldBeArray();
        $all[0]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[0]->getIdentifier()->shouldReturn('foo');
        $all[0]->getType()->shouldReturn(ProductDraft::class);
    }

    function it_checks_if_empty()
    {
        $this->isEmpty()->shouldReturn(true);
    }

    function it_checks_if_not_empty()
    {
        $this->add('foo', ProductDraft::class);
        $this->isEmpty()->shouldReturn(false);
    }

    function it_returns_all_elements_when_empty()
    {
        $this->all()->shouldReturn([]);
    }

    function it_returns_all_elements()
    {
        $this->add('foo', ProductDraft::class);
        $this->add('bar', ProductDraft::class);
        $this->add('baz', ProductDraft::class);

        $all = $this->all();
        $all->shouldHaveCount(3);
        $all->shouldBeArray();
        $all[0]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[1]->shouldBeAnInstanceOf(IdentifierResult::class);
        $all[2]->shouldBeAnInstanceOf(IdentifierResult::class);
    }

    function it_returns_product_identifiers()
    {
        $this->add('foo', ProductDraft::class);
        $this->add('bar', ProductModelDraft::class);
        $this->add('baz', ProductModelDraft::class);
        $this->add('qux', ProductDraft::class);

        $this->getProductIdentifiers()->shouldReturn(['foo', 'qux']);
    }

    function it_returns_product_model_identifiers()
    {
        $this->add('foo', ProductDraft::class);
        $this->add('bar', ProductModelDraft::class);
        $this->add('baz', ProductModelDraft::class);
        $this->add('qux', ProductDraft::class);

        $this->getProductModelIdentifiers()->shouldReturn(['bar', 'baz']);
    }
}
