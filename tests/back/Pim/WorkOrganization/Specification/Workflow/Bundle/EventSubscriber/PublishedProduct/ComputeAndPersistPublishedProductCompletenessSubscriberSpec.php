<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Completeness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\ComputeAndPersistPublishedProductCompletenessSubscriber;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeAndPersistPublishedProductCompletenessSubscriberSpec extends ObjectBehavior
{
    function let(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->beConstructedWith($savePublishedProductCompletenesses, $getProductCompletenesses);
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
        GetProductCompletenesses $getProductCompletenesses,
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses
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
        $product->setId(1);
        $publishedProduct->setOriginalProduct($product);

        $getProductCompletenesses->fromProductId(1)->willReturn(new ProductCompletenessCollection(1, [
            new ProductCompleteness('ecommerce', 'en_US', 1, ['description']),
            new ProductCompleteness('mobile', 'en_US', 2, ['description']),
        ]));

        $savePublishedProductCompletenesses->save(Argument::type(PublishedProductCompletenessCollection::class))->shouldBeCalled();

        $this->computePublishedProductCompleteness(new GenericEvent($publishedProduct));
    }

    function it_does_nothing_for_anything_but_a_published_product(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses
    ) {
        $savePublishedProductCompletenesses->save(Argument::any())->shouldNotBeCalled();

        $this->computePublishedProductCompleteness(new GenericEvent(new \stdClass()));
    }
}
