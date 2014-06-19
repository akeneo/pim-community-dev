<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\MongoDB\Collection
 */
class EnsureIndexesSubscriberSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $managerRegistry,
        DocumentManager $documentManager,
        Collection $collection
    ) {
        $managerRegistry->getManagerForClass('Product')->willReturn($documentManager);
        $documentManager->getDocumentCollection('Product')->willReturn($collection);

        $this->beConstructedWith(
            $managerRegistry,
            'Product',
            'Channel',
            'Locale',
            'Currency',
            'Attribute'
        );
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_doctrine_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['postPersist', 'postUpdate', 'postRemove']);
    }

    function it_generates_scopable_indexes_when_creating_channel(
        $collection,
        $managerRegistry,
        AbstractAttribute $title,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce,
        Channel $mobile,
        EntityManager $entityManager,
        EntityRepository $channelRepo,
        EntityRepository $attributeRepo,
        LifecycleEventArgs $args
    ) {
        $title->getCode()->willReturn('title');
        $title->getBackendType()->willReturn('varchar');
        $title->isLocalizable()->willReturn(false);
        $title->isScopable()->willReturn(true);
        $title->isUseableAsGridFilter()->willReturn(true);
        $title->getAttributeType()->willReturn('pim_catalog_text');

        $en_US->getCode()->willReturn('en_US');
        $en_US->isActivated()->willReturn(true);
        $de_DE->getCode()->willReturn('de_DE');
        $de_DE->isActivated()->willReturn(true);

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn([$en_US, $de_DE]);
        $mobile->getCode()->willReturn('mobile');
        $mobile->getLocales()->willReturn([$en_US]);

        $managerRegistry->getManagerForClass('Attribute')->willReturn($entityManager);
        $entityManager->getRepository('Attribute')->willReturn($attributeRepo);
        $attributeRepo
            ->findBy(['scopable' => true, 'useableAsGridFilter' => true])
            ->willReturn([$title]);

        $managerRegistry->getManagerForClass('Channel')->willReturn($entityManager);
        $entityManager->getRepository('Channel')->willReturn($channelRepo);
        $channelRepo->findAll()->willReturn([$ecommerce, $mobile]);

        $args->getEntity()->willReturn($ecommerce);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-de_DE' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-ecommerce' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-mobile' => 1], $options)->shouldBeCalled();

        $this->postPersist($args);
    }

    function it_generates_localizable_indexes_when_saving_enabled_locale(
        $managerRegistry,
        $collection,
        AbstractAttribute $description,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce,
        EntityRepository $channelRepo,
        EntityRepository $localeRepo,
        EntityManager $entityManager,
        EntityRepository $attributeRepo,
        LifecycleEventArgs $args
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('varchar');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(false);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getAttributeType()->willReturn('pim_catalog_text');

        $en_US->getCode()->willReturn('en_US');
        $en_US->isActivated()->willReturn(true);
        $de_DE->getCode()->willReturn('de_DE');
        $de_DE->isActivated()->willReturn(true);

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn([$en_US, $de_DE]);

        $managerRegistry->getManagerForClass('Channel')->willReturn($entityManager);
        $entityManager->getRepository('Channel')->willReturn($channelRepo);
        $channelRepo->findAll()->willReturn([$ecommerce]);

        $managerRegistry->getManagerForClass('Locale')->willReturn($entityManager);
        $entityManager->getRepository('Locale')->willReturn($localeRepo);
        $localeRepo->findBy(['activated' => true])->willReturn([$en_US, $de_DE]);

        $managerRegistry->getManagerForClass('Attribute')->willReturn($entityManager);
        $entityManager->getRepository('Attribute')->willReturn($attributeRepo);
        $attributeRepo
            ->findBy(['localizable' => true, 'useableAsGridFilter' => true])
            ->willReturn([$description]);

        $args->getEntity()->willReturn($en_US);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-de_DE' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-de_DE' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_prices_indexes_when_saving_enabled_currency(
        $managerRegistry,
        $entityManager,
        $attributeRepo,
        $collection,
        Currency $usd,
        Currency $eur,
        AbstractAttribute $price,
        EntityRepository $currencyRepo,
        EntityManager $entityManager,
        EntityRepository $attributeRepo,
        LifecycleEventArgs $args
    ) {
        $managerRegistry->getManagerForClass('Currency')->willReturn($entityManager);
        $entityManager->getRepository('Currency')->willReturn($currencyRepo);
        $currencyRepo->findBy(['activated' => true])->willReturn([$eur, $usd]);

        $eur->getCode()->willReturn('EUR');
        $eur->isActivated()->willReturn(true);
        $usd->getCode()->willReturn('USD');
        $usd->isActivated()->willReturn(true);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isUseableAsGridFilter()->willReturn(true);
        $price->getAttributeType()->willReturn('pim_catalog_price_collection');

        $managerRegistry->getManagerForClass('Attribute')->willReturn($entityManager);
        $entityManager->getRepository('Attribute')->willReturn($attributeRepo);
        $attributeRepo
            ->findBy(['backendType' => 'prices', 'useableAsGridFilter' => true])
            ->willReturn([$price]);

        $args->getEntity()->willReturn($eur);

        $options =  [
            'background' => true,
            'w'          => 0
        ];
        $collection->ensureIndex(['normalizedData.price.EUR.data' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.price.USD.data' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generates_attribute_indexes_when_saving_filterable_attribute(
        $collection,
        AbstractAttribute $name,
        LifecycleEventArgs $args
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');
        $name->isLocalizable()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');

        $args->getEntity()->willReturn($name);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.name' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
        
    }

    function it_generate_attribute_indexes_when_saving_unique_attribute(
        $collection,
        AbstractAttribute $ean,
        LifecycleEventArgs $args
    ) {
        $ean->getCode()->willReturn('ean');
        $ean->getBackendType()->willReturn('varchar');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isUnique()->willReturn(true);
        $ean->isUseableAsGridFilter()->willReturn(false);
        $ean->getAttributeType()->willReturn('pim_catalog_text');

        $args->getEntity()->willReturn($ean);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.ean' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generate_attribute_indexes_when_saving_identifier_attribute(
        $collection,
        AbstractAttribute $sku,
        LifecycleEventArgs $args
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isUseableAsGridFilter()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $args->getEntity()->willReturn($sku);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.sku' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);

    }

    function it_generate_attribute_indexes_when_saving_filterable_price_attribute(
        $managerRegistry,
        $collection,
        Currency $usd,
        Currency $eur,
        AbstractAttribute $price,
        EntityRepository $currencyRepo,
        EntityManager $entityManager,
        LifecycleEventArgs $args
    ) {
        $managerRegistry->getManagerForClass('Currency')->willReturn($entityManager);
        $entityManager->getRepository('Currency')->willReturn($currencyRepo);
        $currencyRepo->findBy(['activated' => true])->willReturn([$eur, $usd]);

        $eur->getCode()->willReturn('EUR');
        $eur->isActivated()->willReturn(true);
        $usd->getCode()->willReturn('USD');
        $usd->isActivated()->willReturn(true);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isUseableAsGridFilter()->willReturn(true);
        $price->getAttributeType()->willReturn('pim_catalog_price_collection');

        $args->getEntity()->willReturn($price);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.price.EUR.data' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.price.USD.data' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generate_attribute_indexes_when_saving_filterable_option_attribute(
        $collection,
        AbstractAttribute $color,
        LifecycleEventArgs $args
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);
        $color->isUseableAsGridFilter()->willReturn(true);
        $color->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $args->getEntity()->willReturn($color);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.color.id' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generate_attribute_indexes_when_saving_filterable_scopable_attribute(
        $managerRegistry,
        $collection,
        AbstractAttribute $title,
        Channel $ecommerce,
        Channel $mobile,
        EntityRepository $channelRepo,
        EntityManager $entityManager,
        LifecycleEventArgs $args
    ) {
        $ecommerce->getCode()->willReturn('ecommerce');
        $mobile->getCode()->willReturn('mobile');

        $managerRegistry->getManagerForClass('Channel')->willReturn($entityManager);
        $entityManager->getRepository('Channel')->willReturn($channelRepo);
        $channelRepo->findAll()->willReturn([$ecommerce, $mobile]);

        $title->getCode()->willReturn('title');
        $title->getBackendType()->willReturn('varchar');
        $title->isLocalizable()->willReturn(false);
        $title->isScopable()->willReturn(true);
        $title->isUseableAsGridFilter()->willReturn(true);
        $title->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $args->getEntity()->willReturn($title);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.title-ecommerce' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-mobile' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generate_attribute_indexes_when_saving_filterable_localizable_attribute(
        $managerRegistry,
        $collection,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce,
        AbstractAttribute $description,
        EntityRepository $channelRepo,
        EntityRepository $localeRepo,
        EntityManager $entityManager,
        LifecycleEventArgs $args
    ) {
        $en_US->getCode()->willReturn('en_US');
        $en_US->isActivated()->willReturn(true);
        $de_DE->getCode()->willReturn('de_DE');
        $de_DE->isActivated()->willReturn(true);

        $managerRegistry->getManagerForClass('Locale')->willReturn($entityManager);
        $entityManager->getRepository('Locale')->willReturn($localeRepo);
        $localeRepo->findBy(['activated' => true])->willReturn([$en_US, $de_DE]);

        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('varchar');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(false);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $args->getEntity()->willReturn($description);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.description-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-de_DE' => 1], $options)->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_generate_attribute_indexes_when_saving_filterable_scopable_and_localizable_attribute(
        $managerRegistry,
        $collection,
        AbstractAttribute $description,
        Channel $ecommerce,
        Channel $mobile,
        Locale $en_US,
        Locale $de_DE,
        AbstractAttribute $description,
        EntityRepository $channelRepo,
        EntityRepository $localeRepo,
        EntityManager $entityManager,
        LifecycleEventArgs $args
    ) {
        $en_US->getCode()->willReturn('en_US');
        $en_US->isActivated()->willReturn(true);
        $de_DE->getCode()->willReturn('de_DE');
        $de_DE->isActivated()->willReturn(true);

        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLocales()->willReturn([$en_US, $de_DE]);

        $mobile->getCode()->willReturn('mobile');
        $mobile->getLocales()->willReturn([$en_US]);

        $managerRegistry->getManagerForClass('Locale')->willReturn($entityManager);
        $entityManager->getRepository('Locale')->willReturn($localeRepo);
        $localeRepo->findBy(['activated' => true])->willReturn([$en_US, $de_DE]);

        $managerRegistry->getManagerForClass('Channel')->willReturn($entityManager);
        $entityManager->getRepository('Channel')->willReturn($channelRepo);
        $channelRepo->findAll()->willReturn([$ecommerce, $mobile]);

        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('varchar');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(true);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $args->getEntity()->willReturn($description);

        $this->postUpdate($args);
    }

    function it_removes_attribute_indexes_when_attribute_removed(
        $collection,
        AbstractAttribute $title,
        LifecycleEventArgs $args
    ) {
        $title->getCode()->willReturn('title');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($title);

        $collection->deleteIndex('normalizedData.title')->shouldBeCalled();

        $this->postRemove($args);
        
    }

    function it_removes_attribute_indexes_when_scopable_attribute_removed(
        $collection,
        AbstractAttribute $title,
        LifecycleEventArgs $args
    ) {
        $title->getCode()->willReturn('title');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($title);

        $collection->deleteIndex('normalizedData.title-ecommerce')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-mobile')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_option_attribute_removed(
        $collection,
        AbstractAttribute $color,
        LifecycleEventArgs $args
    ) {
        $color->getCode()->willReturn('color');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.color.id" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($color);

        $collection->deleteIndex('normalizedData.color.id')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_scopable_option_attribute_removed(
        $collection,
        AbstractAttribute $color,
        LifecycleEventArgs $args
    ) {
        $color->getCode()->willReturn('color');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.color-de_DE.id" => 1 ] ],
            [ "key" => [ "normalizedData.color-en_US.id" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($color);

        $collection->deleteIndex('normalizedData.color-de_DE.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-en_US.id')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_price_attribute_removed(
        $collection,
        AbstractAttribute $price,
        LifecycleEventArgs $args
    ) {
        $price->getCode()->willReturn('price');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.price.EUR.data" => 1 ] ],
            [ "key" => [ "normalizedData.price.USD.data" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($price);

        $collection->deleteIndex('normalizedData.price.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price.USD.data')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_scopable_price_attribute_removed(
        $collection,
        AbstractAttribute $price,
        LifecycleEventArgs $args
    ) {
        $price->getCode()->willReturn('price');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.price-ecommerce.EUR.data" => 1 ] ],
            [ "key" => [ "normalizedData.price-mobile.USD.data" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($price);

        $collection->deleteIndex('normalizedData.price-ecommerce.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price-mobile.USD.data')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_localizable_attribute_removed(
        $collection,
        AbstractAttribute $title,
        LifecycleEventArgs $args
    ) {
        $title->getCode()->willReturn('title');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($title);

        $collection->deleteIndex('normalizedData.title-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-de_DE')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_attribute_indexes_when_localizable_and_scopable_attribute_removed(
        $collection,
        AbstractAttribute $title,
        LifecycleEventArgs $args
    ) {
        $title->getCode()->willReturn('title');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title-ecommerce-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($title);

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-ecommerce-de_DE')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-mobile-de_DE')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_obsolete_scopable_indexes_when_channel_removed(
        $collection,
        Channel $ecommerce,
        LifecycleEventArgs $args
    ) {
        $ecommerce->getCode()->willReturn('ecommerce');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title-ecommerce-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.price-ecommerce.EUR.data" => 1 ] ],
            [ "key" => [ "normalizedData.price-mobile.USD.data" => 1 ] ],
            [ "key" => [ "normalizedData.name-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.name-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.mobile_support.id" => 1 ] ],
            [ "key" => [ "normalizedData.color-ecommerce.id" => 1 ] ],
            [ "key" => [ "normalizedData.completenesses-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.completenesses-ecommerce-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($ecommerce);

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-ecommerce-de_DE')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price-ecommerce.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.name-ecommerce')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-ecommerce.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-de_DE')->shouldBeCalled();

        $this->postRemove($args);
    }

    function it_removes_obsolete_scopable_indexes_when_locale_disabled(
        $collection,
        Locale $en_US,
        LifecycleEventArgs $args
    ) {
        $en_US->getCode()->willReturn('en_US');
        $en_US->isActivated()->willReturn(false);

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title-ecommerce-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.price-ecommerce.EUR.data" => 1 ] ],
            [ "key" => [ "normalizedData.price-mobile.USD.data" => 1 ] ],
            [ "key" => [ "normalizedData.name-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.name-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.mobile_support.id" => 1 ] ],
            [ "key" => [ "normalizedData.cost-en_US.USD.data" => 1 ] ],
            [ "key" => [ "normalizedData.color-ecommerce-en_US.id" => 1 ] ],
            [ "key" => [ "normalizedData.completenesses-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.completenesses-ecommerce-de_DE" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($en_US);

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title_left-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.cost-en_US.USD.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-ecommerce-en_US.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-en_US')->shouldBeCalled();

        $this->postUpdate($args);
    }

    function it_removes_obsolete_scopable_indexes_when_currency_disabled(
        $collection,
        Currency $usd,
        LifecycleEventArgs $args
    ) {
        $usd->getCode()->willReturn('USD');
        $usd->isActivated()->willReturn(false);

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title-ecommerce-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title-ecommerce-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.title-mobile-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
            [ "key" => [ "normalizedData.price-ecommerce.EUR.data" => 1 ] ],
            [ "key" => [ "normalizedData.price-mobile.USD.data" => 1 ] ],
            [ "key" => [ "normalizedData.name-mobile" => 1 ] ],
            [ "key" => [ "normalizedData.name-ecommerce" => 1 ] ],
            [ "key" => [ "normalizedData.mobile_support.id" => 1 ] ],
            [ "key" => [ "normalizedData.cost-en_US.USD.data" => 1 ] ],
            [ "key" => [ "normalizedData.color-ecommerce-en_US.id" => 1 ] ],
        ]);
        $args->getEntity()->willReturn($usd);

        $collection->deleteIndex('normalizedData.price-mobile.USD.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.cost-en_US.USD.data')->shouldBeCalled();

        $this->postUpdate($args);
    }
}
