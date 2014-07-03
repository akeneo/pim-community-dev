<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexCreator;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexPurger;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\MongoDB\Collection
 */
class EnsureIndexesSubscriberSpec extends ObjectBehavior
{
    function let(
        IndexCreator $indexCreator,
        IndexPurger $indexPurger
    ) {
        $this->beConstructedWith($indexCreator, $indexPurger);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postPersist', 'postUpdate', 'postRemove']);
    }

    function it_generates_indexes_for_unique_attribute_insert(
        $indexCreator,
        AbstractAttribute $ean,
        LifecycleEventArgs $args
    ) {
        $ean->isUseableAsGridFilter()->willReturn(false);
        $ean->getAttributeType()->willReturn('pim_catalog_text');
        $ean->isUnique()->willReturn(true);

        $args->getEntity()->willReturn($ean);

        $indexCreator->ensureIndexesFromAttribute($ean)->shouldBeCalled();

        $this->postPersist($args);
    }

    function it_generates_indexes_for_unique_attribute_update(
        $indexCreator,
        AbstractAttribute $ean,
        LifecycleEventArgs $args
    ) {
        $ean->isUseableAsGridFilter()->willReturn(false);
        $ean->getAttributeType()->willReturn('pim_catalog_text');
        $ean->isUnique()->willReturn(true);

        $args->getEntity()->willReturn($ean);

        $indexCreator->ensureIndexesFromAttribute($ean)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_indexes_for_identifier_attribute_insert(
        $indexCreator,
        AbstractAttribute $sku,
        LifecycleEventArgs $args
    ) {
        $sku->isUseableAsGridFilter()->willReturn(false);
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($sku);

        $indexCreator->ensureIndexesFromAttribute($sku)->shouldBeCalled();

        $this->postPersist($args);
    }

    function it_generates_indexes_for_identifier_attribute_update(
        $indexCreator,
        AbstractAttribute $sku,
        LifecycleEventArgs $args
    ) {
        $sku->isUseableAsGridFilter()->willReturn(false);
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($sku);

        $indexCreator->ensureIndexesFromAttribute($sku)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_indexes_for_filterable_attribute_insert(
        $indexCreator,
        AbstractAttribute $name,
        LifecycleEventArgs $args
    ) {
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($name);

        $indexCreator->ensureIndexesFromAttribute($name)->shouldBeCalled();

        $this->postPersist($args);
    }

    function it_generates_indexes_for_filterable_attribute_update(
        $indexCreator,
        AbstractAttribute $name,
        LifecycleEventArgs $args
    ) {
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($name);

        $indexCreator->ensureIndexesFromAttribute($name)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_does_not_generates_indexes_for_other_attribute_insert(
        $indexCreator,
        AbstractAttribute $description,
        LifecycleEventArgs $args
    ) {
        $description->isUseableAsGridFilter()->willReturn(false);
        $description->getAttributeType()->willReturn('pim_catalog_textarea');
        $description->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($description);

        $indexCreator->ensureIndexesFromAttribute($description)->shouldNotBeCalled();

        $this->postPersist($args);
    }

    function it_does_not_generates_indexes_for_other_attribute_update(
        $indexCreator,
        AbstractAttribute $description,
        LifecycleEventArgs $args
    ) {
        $description->isUseableAsGridFilter()->willReturn(false);
        $description->getAttributeType()->willReturn('pim_catalog_textarea');
        $description->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($description);

        $indexCreator->ensureIndexesFromAttribute($description)->shouldNotBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_indexes_for_channel_insert(
        $indexCreator,
        Channel $ecommerce,
        LifecycleEventArgs $args
    ) {
        $args->getEntity()->willReturn($ecommerce);

        $indexCreator->ensureIndexesFromChannel($ecommerce)->shouldBeCalled();

        $this->postPersist($args);
    }

    function it_generates_indexes_for_channel_update(
        $indexCreator,
        Channel $ecommerce,
        LifecycleEventArgs $args
    ) {
        $args->getEntity()->willReturn($ecommerce);

        $indexCreator->ensureIndexesFromChannel($ecommerce)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_removes_indexes_for_channel_when_removing_it(
        $indexPurger,
        Channel $ecommerce,
        LifecycleEventArgs $args
    ) {
        $args->getEntity()->willReturn($ecommerce);

        $indexPurger->purgeIndexesFromChannel($ecommerce)->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_generates_indexes_for_locale_when_enabling_it(
        $indexCreator,
        Locale $en_US,
        LifecycleEventArgs $args
    ) {
        $en_US->isActivated()->willReturn(true);
        $args->getEntity()->willReturn($en_US);

        $indexCreator->ensureIndexesFromLocale($en_US)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_removes_indexes_for_locale_when_disabling_it(
        $indexPurger,
        Locale $en_US,
        LifecycleEventArgs $args
    ) {
        $en_US->isActivated()->willReturn(false);
        $args->getEntity()->willReturn($en_US);

        $indexPurger->purgeIndexesFromLocale($en_US)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_indexes_for_currency_when_enablig_it(
        $indexCreator,
        Currency $usd,
        LifecycleEventArgs $args
    ) {
        $usd->isActivated()->willReturn(true);
        $args->getEntity()->willReturn($usd);

        $indexCreator->ensureIndexesFromCurrency($usd)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_removes_indexes_for_currency_when_disabling_it(
        $indexPurger,
        Currency $usd,
        LifecycleEventArgs $args
    ) {
        $usd->isActivated()->willReturn(false);
        $args->getEntity()->willReturn($usd);

        $indexPurger->purgeIndexesFromCurrency($usd)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_removes_indexes_for_attribute_when_removing_it(
        $indexPurger,
        AbstractAttribute $name,
        LifecycleEventArgs $args
    ) {
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');
        $name->isUnique()->willReturn(false);

        $args->getEntity()->willReturn($name);

        $indexPurger->purgeIndexesFromAttribute($name)->shouldBeCalled();

        $this->postRemove($args);
    }
}
