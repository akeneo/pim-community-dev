<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\ComputeAndPersistPublishedProductCompletenessSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeAndPersistPublishedProductCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator
    ) {
        $this->beConstructedWith($savePublishedProductCompletenesses, $completenessCalculator);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_is_a_compute_published_product_completeness_event_subscriber()
    {
        $this->shouldHaveType(ComputeAndPersistPublishedProductCompletenessSubscriber::class);
    }

    function it_computes_and_saves_completenesses_for_a_published_product(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator
    ) {
        $publishedProduct = new PublishedProduct();
        $publishedProduct->setId(42);
        $ecommerce = new Channel();
        $ecommerce->setCode('ecommerce');
        $frFr = new Locale();
        $frFr->setCode('fr_FR');
        $enUs = new Locale();
        $enUs->setCode('en_US');
        $description = new Attribute();
        $description->setCode('description');
        $picture = new Attribute();
        $picture->setCode('picture');
        $product = new Product();
        $product->addValue(ScalarValue::value('identifier', 'my_published_product'));
        $product->setIdentifier('my_published_product');
        $publishedProduct->setOriginalProduct($product);

        $completenessCalculator->fromProductIdentifier('my_published_product')->willReturn(
            new ProductCompletenessWithMissingAttributeCodesCollection(
                1, [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 1, ['description']),
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 2, ['description']),
            ])
        );

        $savePublishedProductCompletenesses
            ->save(Argument::type(PublishedProductCompletenessCollection::class))
            ->shouldBeCalled();

        $this->computePublishedProductCompleteness(new GenericEvent($publishedProduct));
    }

    function it_does_nothing_for_anything_but_a_published_product(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses
    ) {
        $savePublishedProductCompletenesses->save(Argument::any())->shouldNotBeCalled();

        $this->computePublishedProductCompleteness(new GenericEvent(new \stdClass()));
    }
}
