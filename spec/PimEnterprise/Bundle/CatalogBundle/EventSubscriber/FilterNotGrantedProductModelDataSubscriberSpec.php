<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Component\Security\NotGrantedDataFilterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FilterNotGrantedProductModelDataSubscriberSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_implements_the_event_subscriber()
    {
        $this->shouldBeAnInstanceOf(EventSubscriber::class);
    }

    function it_subscribes_to_post_load_events()
    {
        $this->getSubscribedEvents()->shouldReturn([Events::postLoad]);
    }

    function it_does_nothing_if_loaded_entity_is_not_a_product_model(
        $container,
        LifecycleEventArgs $event,
        \stdClass $randomObject
    ) {
        $event->getObject()->willReturn($randomObject);

        $container->get('pimee_catalog.security.filter.not_granted_category')
            ->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_does_not_filter_product_model_on_post_load_if_it_has_no_category(
        $container,
        LifecycleEventArgs $event,
        ProductModelInterface $productModel,
        ArrayCollection $categories
    ) {
        $event->getObject()->willReturn($productModel);
        $productModel->getCategories()->willReturn($categories);
        $categories->count()->willReturn(0);

        $container->get('pimee_catalog.security.filter.not_granted_category')
            ->shouldNotBeCalled();

        $this->postLoad($event);
    }

    function it_filters_product_model_on_post_load_if_it_has_category(
        $container,
        LifecycleEventArgs $event,
        ProductModelInterface $productModel,
        ArrayCollection $categories,
        NotGrantedDataFilterInterface $filter
    ) {
        $event->getObject()->willReturn($productModel);
        $productModel->getCategories()->willReturn($categories);
        $categories->count()->willReturn(2);
        $container->get('pimee_catalog.security.filter.not_granted_category')
            ->willReturn($filter);

        $filter->filter($productModel)->shouldBeCalled();

        $this->postLoad($event);
    }
}
