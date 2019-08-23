<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use PhpSpec\ObjectBehavior;

class ProductCompletenessWithMissingAttributeCodesCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(42, []);
    }

    function it_is_a_product_completeness_collection()
    {
        $this->shouldHaveType(ProductCompletenessWithMissingAttributeCodesCollection::class);
    }

    function it_is_an_iterator_aggregate()
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    function it_can_only_store_product_completenesses()
    {
        $this->beConstructedWith(42, [new \stdClass()]);
        $this->shouldThrow(\TypeError::class)->duringInstantiation();
    }

    function it_exposes_a_product_id()
    {
        $this->productId()->shouldReturn(42);
    }

    function it_can_store_product_completenesses()
    {
        $completeness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 4, []);
        $otherCompleteness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 4, []);
        $this->beConstructedWith(42, [$completeness, $otherCompleteness]);

        $this->getIterator()->count()->shouldReturn(2);
        $this->getIterator()->getArrayCopy()->shouldReturn([
            'ecommerce-en_US' => $completeness,
            'ecommerce-fr_FR' => $otherCompleteness,
        ]);
    }

    function it_does_not_store_two_completenesses_with_the_same_channel_and_locale()
    {
        $completeness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 4, []);
        $otherCompleteness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['description', 'price']);
        $this->beConstructedWith(42, [$completeness, $otherCompleteness]);

        $this->getIterator()->count()->shouldReturn(1);
        $this->getIterator()->getArrayCopy()->shouldReturn(['ecommerce-en_US' => $otherCompleteness]);
    }

    function it_can_retrieve_a_completeness_by_channel_and_locale()
    {
        $completeness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 4, []);
        $otherCompleteness = new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'fr_FR', 4, []);
        $this->beConstructedWith(42, [$completeness, $otherCompleteness]);

        $this->getCompletenessForChannelAndLocale('ecommerce', 'fr_FR')->shouldReturn($otherCompleteness);
        $this->getCompletenessForChannelAndLocale('other_channel', 'en_US')->shouldReturn(null);
    }
}
