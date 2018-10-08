<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

class IdentifierResultSpec extends ObjectBehavior
{
    function it_gets_the_identifier()
    {
        $this->beConstructedWith('foo', ProductDraft::class);
        $this->getIdentifier()->shouldReturn('foo');
    }

    function it_gets_the_type()
    {
        $this->beConstructedWith('foo', ProductDraft::class);
        $this->getType()->shouldReturn(ProductDraft::class);
    }

    function it_determines_if_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductDraft::class);
        $this->isProductDraftIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_identifier()
    {
        $this->beConstructedWith('foo', ProductModelDraft::class);
        $this->isProductDraftIdentifierEquals('foo')->shouldReturn(false);
    }

    function it_determines_if_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductModelDraft::class);
        $this->isProductModelDraftIdentifierEquals('foo')->shouldReturn(true);
    }

    function it_determines_if_not_equals_to_a_product_model_identifier()
    {
        $this->beConstructedWith('foo', ProductDraft::class);
        $this->isProductModelDraftIdentifierEquals('foo')->shouldReturn(false);
    }
}
