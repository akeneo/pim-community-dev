<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\MongoDB\Collection
 */
class IndexCreatorSpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $managerRegistry,
        DocumentManager $documentManager,
        NamingUtility $namingUtility,
        Collection $collection
    ) {
        $managerRegistry->getManagerForClass('Product')->willReturn($documentManager);
        $documentManager->getDocumentCollection('Product')->willReturn($collection);

        $this->beConstructedWith(
            $managerRegistry,
            $namingUtility,
            'Product'
        );
    }

    function it_generates_scopable_indexes_when_creating_channel(
        $collection,
        $namingUtility,
        AbstractAttribute $title,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce,
        Channel $mobile
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

        $namingUtility->getScopableAttributes()->willReturn([$title]);
        $namingUtility
            ->getAttributeNormFields($title)
            ->willReturn(['normalizedData.title-ecommerce', 'normalizedData.title-mobile']);

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
        $collection,
        $namingUtility,
        AbstractAttribute $description,
        Locale $en_US,
        Locale $de_DE,
        Channel $ecommerce
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

        $namingUtility->getChannels()->willReturn([$ecommerce]);
        $namingUtility->getLocalizableAttributes()->willReturn([$description]);
        $namingUtility
            ->getAttributeNormFields($description)
            ->willReturn(['normalizedData.description-en_US', 'normalizedData.description-de_DE']);

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
        $collection,
        $namingUtility,
        Currency $eur,
        AbstractAttribute $price
    ) {
        $eur->getCode()->willReturn('EUR');
        $eur->isActivated()->willReturn(true);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isUseableAsGridFilter()->willReturn(true);
        $price->getAttributeType()->willReturn('pim_catalog_price_collection');

        $namingUtility->getPricesAttributes()->willReturn([$price]);
        $namingUtility->getCurrencyCodes()->willReturn(['EUR', 'USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['EUR', 'USD'], '.')
            ->willReturn(['normalizedData.price.EUR', 'normalizedData.price.USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['data'], '.')
            ->willReturn(['normalizedData.price.EUR.data', 'normalizedData.price.USD.data']);
        $namingUtility->getAttributeNormFields($price)->willReturn(['normalizedData.price', 'normalizedData.price']);

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
        $namingUtility,
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

        $namingUtility->getAttributeNormFields($name)->willReturn(['normalizedData.name']);

        $collection->ensureIndex(['normalizedData.name' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($name);
    }

    function it_generates_attribute_indexes_when_saving_unique_attribute(
        $collection,
        $namingUtility,
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

        $namingUtility->getAttributeNormFields($ean)->willReturn(['normalizedData.ean']);

        $collection->ensureIndex(['normalizedData.ean' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($ean);
    }

    function it_generates_attribute_indexes_when_saving_identifier_attribute(
        $collection,
        $namingUtility,
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

        $namingUtility->getAttributeNormFields($sku)->willReturn(['normalizedData.sku']);

        $collection->ensureIndex(['normalizedData.sku' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($sku);
    }

    function it_generates_attribute_indexes_when_saving_filterable_price_attribute(
        $namingUtility,
        $collection,
        AbstractAttribute $price
    ) {
        $namingUtility->getPricesAttributes()->willReturn([$price]);
        $namingUtility->getAttributeNormFields($price)->willReturn(['normalizedData.price', 'normalizedData.price']);
        $namingUtility->getCurrencyCodes()->willReturn(['EUR', 'USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['EUR', 'USD'], '.')
            ->willReturn(['normalizedData.price.EUR', 'normalizedData.price.USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['data'], '.')
            ->willReturn(['normalizedData.price.EUR.data', 'normalizedData.price.USD.data']);

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
        $namingUtility,
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

        $namingUtility->getAttributeNormFields($color)->willReturn(['normalizedData.color']);
        $namingUtility->appendSuffixes(['normalizedData.color'], ['id'], '.')->willReturn(['normalizedData.color.id']);
        $collection->ensureIndex(['normalizedData.color.id' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($color);
    }

    function it_generates_attribute_indexes_when_saving_filterable_scopable_attribute(
        $namingUtility,
        $collection,
        AbstractAttribute $title,
        Channel $ecommerce,
        Channel $mobile
    ) {
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

        $namingUtility
            ->getAttributeNormFields($title)
            ->willReturn(['normalizedData.title-ecommerce', 'normalizedData.title-mobile']);
        $collection->ensureIndex(['normalizedData.title-ecommerce' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.title-mobile' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($title);
    }

    function it_generates_attribute_indexes_when_saving_filterable_localizable_attribute(
        $collection,
        $namingUtility,
        AbstractAttribute $description
    ) {
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

        $namingUtility
            ->getAttributeNormFields($description)
            ->willReturn(['normalizedData.description-en_US', 'normalizedData.description-de_DE']);
        $collection->ensureIndex(['normalizedData.description-en_US' => 1], $options)->shouldBeCalled();
        $collection->ensureIndex(['normalizedData.description-de_DE' => 1], $options)->shouldBeCalled();

        $this->ensureIndexesFromAttribute($description);
    }

    function it_generates_attribute_indexes_when_saving_filterable_scopable_and_localizable_attribute(
        $collection,
        $namingUtility,
        AbstractAttribute $description
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('varchar');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(true);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $namingUtility
            ->getAttributeNormFields($description)
            ->willReturn(
                [
                    'normalizedData.description-en_US-ecommerce',
                    'normalizedData.description-de_DE-ecommerce',
                    'normalizedData.description-en_US-mobile'
                ]
            );

        $this->ensureIndexesFromAttribute($description);
    }
}
