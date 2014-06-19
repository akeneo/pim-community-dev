<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
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
class IndexCreatorSpec extends ObjectBehavior
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
        EntityRepository $attributeRepo
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-de_DE' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-ecommerce' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-mobile' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromChannel($ecommerce);
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
        EntityRepository $attributeRepo
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.completenesses.ecommerce-de_DE' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-de_DE' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromLocale($en_US);
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
        EntityRepository $attributeRepo
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];
        $collection->ensureIndex(['normalizedData.price.EUR.data' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.price.USD.data' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromCurrency($eur);
    }

    function it_generates_attribute_indexes_when_saving_filterable_attribute(
        $collection,
        AbstractAttribute $name
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('varchar');
        $name->isLocalizable()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getAttributeType()->willReturn('pim_catalog_text');

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.name' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($name);
    }

    function it_generates_attribute_indexes_when_saving_unique_attribute(
        $collection,
        AbstractAttribute $ean
    ) {
        $ean->getCode()->willReturn('ean');
        $ean->getBackendType()->willReturn('varchar');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isUnique()->willReturn(true);
        $ean->isUseableAsGridFilter()->willReturn(false);
        $ean->getAttributeType()->willReturn('pim_catalog_text');

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.ean' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($ean);
    }

    function it_generates_attribute_indexes_when_saving_identifier_attribute(
        $collection,
        AbstractAttribute $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('varchar');
        $sku->getAttributeType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isUseableAsGridFilter()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.sku' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($sku);
    }

    function it_generates_attribute_indexes_when_saving_filterable_price_attribute(
        $managerRegistry,
        $collection,
        Currency $usd,
        Currency $eur,
        AbstractAttribute $price,
        EntityRepository $currencyRepo,
        EntityManager $entityManager
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.price.EUR.data' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.price.USD.data' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($price);
    }

    function it_generates_attribute_indexes_when_saving_filterable_option_attribute(
        $collection,
        AbstractAttribute $color
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);
        $color->isUseableAsGridFilter()->willReturn(true);
        $color->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.color.id' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($color);
    }

    function it_generates_attribute_indexes_when_saving_filterable_scopable_attribute(
        $managerRegistry,
        $collection,
        AbstractAttribute $title,
        Channel $ecommerce,
        Channel $mobile,
        EntityRepository $channelRepo,
        EntityManager $entityManager
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.title-ecommerce' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-mobile' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($title);
    }

    function it_generates_attribute_indexes_when_saving_filterable_localizable_attribute(
        $managerRegistry,
        $collection,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce,
        AbstractAttribute $description,
        EntityRepository $channelRepo,
        EntityRepository $localeRepo,
        EntityManager $entityManager
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

        $options =  [
            'background' => true,
            'w'          => 0
        ];

        $collection->ensureIndex(['normalizedData.description-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-de_DE' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($description);
    }

    function it_generates_attribute_indexes_when_saving_filterable_scopable_and_localizable_attribute(
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
        EntityManager $entityManager
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

        $this->ensureIndexesFromAttribute($description);
    }
}
