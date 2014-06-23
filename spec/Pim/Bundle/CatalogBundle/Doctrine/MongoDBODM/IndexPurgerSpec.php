<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Doctrine\MongoDB\Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\MongoDB\Collection
 */
class IndexPurgerSpec extends ObjectBehavior
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

    function it_removes_attribute_indexes_when_an_attribute_is_removed(
        $collection,
        AbstractAttribute $title
    ) {
        $title->getCode()->willReturn('title');

        $collection->getIndexInfo()->willReturn([
            [ "key" => [ "_id" => 1, ] ],
            [ "key" => [ "normalizedData.title" => 1 ] ],
            [ "key" => [ "normalizedData.manufacturer_title" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-en_US" => 1 ] ],
            [ "key" => [ "normalizedData.title_left-de_DE" => 1 ] ],
        ]);

        $collection->deleteIndex('normalizedData.title')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($title);
    }

    function it_removes_attribute_indexes_when_a_scopable_attribute_is_removed(
        $collection,
        AbstractAttribute $title
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

        $collection->deleteIndex('normalizedData.title-ecommerce')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-mobile')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($title);
    }

    function it_removes_attribute_indexes_when_an_option_attribute_is_removed(
        $collection,
        AbstractAttribute $color
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

        $collection->deleteIndex('normalizedData.color.id')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($color);
    }

    function it_removes_attribute_indexes_when_a_scopable_option_attribute_is_removed(
        $collection,
        AbstractAttribute $color
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

        $collection->deleteIndex('normalizedData.color-de_DE.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-en_US.id')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($color);
    }

    function it_removes_attribute_indexes_when_a_price_attribute_is_removed(
        $collection,
        AbstractAttribute $price
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

        $collection->deleteIndex('normalizedData.price.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price.USD.data')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($price);
    }

    function it_removes_attribute_indexes_when_a_scopable_price_attribute_is_removed(
        $collection,
        AbstractAttribute $price
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

        $collection->deleteIndex('normalizedData.price-ecommerce.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price-mobile.USD.data')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($price);
    }

    function it_removes_attribute_indexes_when_a_localizable_attribute_is_removed(
        $collection,
        AbstractAttribute $title
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

        $collection->deleteIndex('normalizedData.title-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-de_DE')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($title);
    }

    function it_removes_attribute_indexes_when_a_localizable_and_scopable_attribute_is_removed(
        $collection,
        AbstractAttribute $title
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

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-ecommerce-de_DE')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-mobile-de_DE')->shouldBeCalled();

        $this->purgeIndexesFromAttribute($title);
    }

    function it_removes_obsolete_scopable_indexes_when_channel_removed(
        $collection,
        Channel $ecommerce
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

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title-ecommerce-de_DE')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.price-ecommerce.EUR.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.name-ecommerce')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-ecommerce.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-de_DE')->shouldBeCalled();

        $this->purgeIndexesFromChannel($ecommerce);
    }

    function it_removes_obsolete_localizable_indexes_when_locale_is_disabled(
        $collection,
        Locale $en_US
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

        $collection->deleteIndex('normalizedData.title-ecommerce-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.title_left-en_US')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.cost-en_US.USD.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.color-ecommerce-en_US.id')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.completenesses-ecommerce-en_US')->shouldBeCalled();

        $this->purgeIndexesFromLocale($en_US);
    }

    function it_removes_obsolete_price_indexes_when_currency_is_disabled(
        $collection,
        Currency $usd
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

        $collection->deleteIndex('normalizedData.price-mobile.USD.data')->shouldBeCalled();
        $collection->deleteIndex('normalizedData.cost-en_US.USD.data')->shouldBeCalled();

        $this->purgeIndexesFromCurrency($usd);
    }
}
