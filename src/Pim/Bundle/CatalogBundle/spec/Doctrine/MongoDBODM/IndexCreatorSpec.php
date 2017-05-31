<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\CurrencyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;
use Prophecy\Argument;

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
        Collection $collection,
        LoggerInterface $logger
    ) {
        $managerRegistry->getManagerForClass('Product')->willReturn($documentManager);
        $documentManager->getDocumentCollection('Product')->willReturn($collection);

        $this->beConstructedWith(
            $managerRegistry,
            $namingUtility,
            'Product',
            $logger,
            'Attribute'
        );
    }

    function it_generates_scopable_indexes_when_creating_channel(
        $collection,
        $namingUtility,
        AttributeInterface $title,
        LocaleInterface $en_US,
        LocaleInterface $de_DE,
        ChannelInterface $ecommerce,
        ChannelInterface $mobile
    ) {
        $title->getCode()->willReturn('title');
        $title->getBackendType()->willReturn('text');
        $title->isLocalizable()->willReturn(false);
        $title->isScopable()->willReturn(true);
        $title->isUseableAsGridFilter()->willReturn(true);
        $title->getType()->willReturn('pim_catalog_text');

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

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $description,
        LocaleInterface $en_US,
        LocaleInterface $de_DE,
        ChannelInterface $ecommerce
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(false);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getType()->willReturn('pim_catalog_text');

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

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        CurrencyInterface $eur,
        AttributeInterface $price
    ) {
        $eur->getCode()->willReturn('EUR');
        $eur->isActivated()->willReturn(true);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isUseableAsGridFilter()->willReturn(true);
        $price->getType()->willReturn('pim_catalog_price_collection');

        $namingUtility->getPricesAttributes()->willReturn([$price]);
        $namingUtility->getCurrencyCodes()->willReturn(['EUR', 'USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['EUR', 'USD'], '.')
            ->willReturn(['normalizedData.price.EUR', 'normalizedData.price.USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price.EUR', 'normalizedData.price.USD'], ['data'], '.')
            ->willReturn(['normalizedData.price.EUR.data', 'normalizedData.price.USD.data']);

        $namingUtility->getAttributeNormFields($price)->willReturn(['normalizedData.price', 'normalizedData.price']);

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $name
    ) {
        $name->getCode()->willReturn('name');
        $name->getBackendType()->willReturn('text');
        $name->isLocalizable()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->isUseableAsGridFilter()->willReturn(true);
        $name->getType()->willReturn('pim_catalog_text');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $ean
    ) {
        $ean->getCode()->willReturn('ean');
        $ean->getBackendType()->willReturn('text');
        $ean->isLocalizable()->willReturn(false);
        $ean->isScopable()->willReturn(false);
        $ean->isUnique()->willReturn(true);
        $ean->isUseableAsGridFilter()->willReturn(false);
        $ean->getType()->willReturn('pim_catalog_text');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('text');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isUseableAsGridFilter()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $price
    ) {
        $namingUtility->getPricesAttributes()->willReturn([$price]);
        $namingUtility->getAttributeNormFields($price)->willReturn(['normalizedData.price', 'normalizedData.price']);
        $namingUtility->getCurrencyCodes()->willReturn(['EUR', 'USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price', 'normalizedData.price'], ['EUR', 'USD'], '.')
            ->willReturn(['normalizedData.price.EUR', 'normalizedData.price.USD']);
        $namingUtility
            ->appendSuffixes(['normalizedData.price.EUR', 'normalizedData.price.USD'], ['data'], '.')
            ->willReturn(['normalizedData.price.EUR.data', 'normalizedData.price.USD.data']);

        $price->getCode()->willReturn('price');
        $price->getBackendType()->willReturn('prices');
        $price->isLocalizable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isUseableAsGridFilter()->willReturn(true);
        $price->getType()->willReturn('pim_catalog_price_collection');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->getBackendType()->willReturn('option');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);
        $color->isUseableAsGridFilter()->willReturn(true);
        $color->getType()->willReturn('pim_catalog_simpleselect');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $title
    ) {
        $title->getCode()->willReturn('title');
        $title->getBackendType()->willReturn('text');
        $title->isLocalizable()->willReturn(false);
        $title->isScopable()->willReturn(true);
        $title->isUseableAsGridFilter()->willReturn(true);
        $title->getType()->willReturn('pim_catalog_simpleselect');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        AttributeInterface $description
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(false);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getType()->willReturn('pim_catalog_simpleselect');

        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $options = [
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
        $namingUtility,
        AttributeInterface $description
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(true);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getType()->willReturn('pim_catalog_simpleselect');

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

    function it_generates_completeness_indexes(
        $namingUtility,
        $collection,
        ChannelInterface $channelWeb,
        ChannelInterface $channelPrint,
        LocaleInterface $localeFr,
        LocaleInterface $localeEn
    ) {
        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);
        $namingUtility->getChannels()->willReturn([$channelWeb, $channelPrint]);

        $channelWeb->getLocales()->willReturn([$localeEn]);
        $channelPrint->getLocales()->willReturn([$localeFr, $localeEn]);
        $channelPrint->getCode()->willReturn('PRINT');
        $channelWeb->getCode()->willReturn('WEB');
        $localeEn->getCode()->willReturn('en_US');
        $localeFr->getCode()->willReturn('fr_FR');

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        $collection
            ->ensureIndex(['normalizedData.completenesses.PRINT-en_US' => 1], $indexOptions)
            ->shouldBeCalled();
        $collection
            ->ensureIndex(['normalizedData.completenesses.PRINT-fr_FR' => 1], $indexOptions)
            ->shouldBeCalled();
        $collection
            ->ensureIndex(['normalizedData.completenesses.WEB-en_US' => 1], $indexOptions)
            ->shouldBeCalled();

        $this->ensureCompletenessesIndexes();
    }

    function it_generates_unique_attribute_indexes(
        $namingUtility,
        $collection,
        $managerRegistry,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $sku,
        AttributeInterface $ean
    ) {
        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);

        $managerRegistry->getRepository('Attribute')->willReturn($attributeRepository);
        $attributeRepository
            ->findBy(['unique' => true], ['created' => 'ASC'], 64)
            ->willReturn([$sku, $ean]);

        $namingUtility->getAttributeNormFields($sku)->willReturn(['normalizedData.sku']);
        $namingUtility->getAttributeNormFields($ean)->willReturn(['normalizedData.ean']);

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        $collection
            ->ensureIndex(['normalizedData.sku' => 1], $indexOptions)
            ->shouldBeCalled();
        $collection
            ->ensureIndex(['normalizedData.ean' => 1], $indexOptions)
            ->shouldBeCalled();

        $this->ensureUniqueAttributesIndexes();
    }

    function it_generates_attribute_indexes(
        $namingUtility,
        $collection,
        $managerRegistry,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $name,
        AttributeInterface $description
    ) {
        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);

        $managerRegistry->getRepository('Attribute')->willReturn($attributeRepository);
        $attributeRepository
            ->findBy(['useableAsGridFilter' => true], ['created' => 'ASC'], 64)
            ->willReturn([$name, $description]);

        $namingUtility->getAttributeNormFields($name)->willReturn(['normalizedData.name']);
        $namingUtility->getAttributeNormFields($description)->willReturn(['normalizedData.description']);

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        $collection
            ->ensureIndex(['normalizedData.name' => 1], $indexOptions)
            ->shouldBeCalled();
        $collection
            ->ensureIndex(['normalizedData.description' => 1], $indexOptions)
            ->shouldBeCalled();

        $this->ensureAttributesIndexes();
    }

    function it_generates_hashed_indexes_for_text_attribute(
        $namingUtility,
        $collection,
        $managerRegistry,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $name,
        AttributeInterface $description
    ) {
        $indexes = array_fill(0, 10, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);

        $managerRegistry->getRepository('Attribute')->willReturn($attributeRepository);
        $attributeRepository
            ->findBy(['useableAsGridFilter' => true], ['created' => 'ASC'], 64)
            ->willReturn([$name, $description]);
        $name->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $description->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_TEXTAREA);

        $namingUtility->getAttributeNormFields($name)->willReturn(['normalizedData.name']);
        $namingUtility->getAttributeNormFields($description)->willReturn(['normalizedData.description']);

        $indexOptions = [
            'background' => true,
            'w'          => 0
        ];

        $collection
            ->ensureIndex(['normalizedData.name' => 1], $indexOptions)
            ->shouldBeCalled();
        $collection
            ->ensureIndex(['normalizedData.description' => 'hashed'], $indexOptions)
            ->shouldBeCalled();

        $this->ensureAttributesIndexes();
    }

    function it_logs_error_when_the_maximum_number_of_indexes_is_reached(
        $collection,
        AttributeInterface $description,
        $namingUtility,
        $logger
    ) {
        $description->getCode()->willReturn('description');
        $description->getBackendType()->willReturn('textarea');
        $description->isLocalizable()->willReturn(true);
        $description->isScopable()->willReturn(false);
        $description->isUseableAsGridFilter()->willReturn(true);
        $description->getType()->willReturn('pim_catalog_textarea');

        $namingUtility
            ->getAttributeNormFields($description)
            ->willReturn(['normalizedData.description-en_US', 'normalizedData.description-de_DE']);

        $indexes = array_fill(0, 64, 'fake_index');
        $collection->getIndexInfo()->willReturn($indexes);

        $logger->error(Argument::any())->shouldBeCalled();
        $collection->ensureIndex(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->ensureIndexesFromAttribute($description);
    }
}
