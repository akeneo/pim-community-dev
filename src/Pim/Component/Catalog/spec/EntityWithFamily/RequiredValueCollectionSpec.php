<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class RequiredValueCollectionSpec extends ObjectBehavior
{
    function let(
        AttributeInterface $length,
        AttributeInterface $price,
        AttributeInterface $description,
        AttributeInterface $releaseDate,
        AttributeInterface $image,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR,
        ValueInterface $value1,
        ValueInterface $value2,
        ValueInterface $value3,
        ValueInterface $value4,
        ValueInterface $value5,
        ValueInterface $value6,
        ValueInterface $value7
    ) {
        $length->isUnique()->willReturn(false);
        $price->isUnique()->willReturn(false);
        $description->isUnique()->willReturn(false);
        $releaseDate->isUnique()->willReturn(true);
        $image->isUnique()->willReturn(false);

        $length->isScopable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $description->isScopable()->willReturn(true);
        $releaseDate->isScopable()->willReturn(false);
        $image->isScopable()->willReturn(false);

        $length->isLocalizable()->willReturn(false);
        $price->isLocalizable()->willReturn(false);
        $description->isLocalizable()->willReturn(true);
        $releaseDate->isLocalizable()->willReturn(false);
        $image->isLocalizable()->willReturn(false);

        $length->isLocaleSpecific()->willReturn(false);
        $price->isLocaleSpecific()->willReturn(false);
        $description->isLocaleSpecific()->willReturn(false);
        $releaseDate->isLocaleSpecific()->willReturn(false);
        $image->isLocaleSpecific()->willReturn(true);

        $length->hasLocaleSpecific($fr_FR)->willReturn(false);
        $length->hasLocaleSpecific($en_US)->willReturn(false);
        $price->hasLocaleSpecific($fr_FR)->willReturn(false);
        $price->hasLocaleSpecific($en_US)->willReturn(false);
        $description->hasLocaleSpecific($fr_FR)->willReturn(false);
        $description->hasLocaleSpecific($en_US)->willReturn(false);
        $releaseDate->hasLocaleSpecific($fr_FR)->willReturn(false);
        $releaseDate->hasLocaleSpecific($en_US)->willReturn(false);
        $image->hasLocaleSpecific($fr_FR)->willReturn(true);
        $image->hasLocaleSpecific($en_US)->willReturn(false);

        $length->getCode()->willReturn('length');
        $price->getCode()->willReturn('price');
        $description->getCode()->willReturn('description');
        $ecommerce->getCode()->willReturn('ecommerce');
        $releaseDate->getCode()->willReturn('release_date');
        $image->getCode()->willReturn('image');
        $print->getCode()->willReturn('print');
        $en_US->getCode()->willReturn('en_US');
        $fr_FR->getCode()->willReturn('fr_FR');

        $value1->getAttribute()->willReturn($length);
        $value2->getAttribute()->willReturn($price);
        $value3->getAttribute()->willReturn($description);
        $value4->getAttribute()->willReturn($description);
        $value5->getAttribute()->willReturn($description);
        $value6->getAttribute()->willReturn($releaseDate);
        $value7->getAttribute()->willReturn($image);

        $value1->getScope()->willReturn(null);
        $value2->getScope()->willReturn(null);
        $value3->getScope()->willReturn('ecommerce');
        $value4->getScope()->willReturn('ecommerce');
        $value5->getScope()->willReturn('print');
        $value6->getScope()->willReturn(null);
        $value7->getScope()->willReturn(null);

        $value1->getLocale()->willReturn(null);
        $value2->getLocale()->willReturn(null);
        $value3->getLocale()->willReturn('en_US');
        $value4->getLocale()->willReturn('fr_FR');
        $value5->getLocale()->willReturn('en_US');
        $value6->getLocale()->willReturn(null);
        $value7->getLocale()->willReturn('fr_FR');

        $value6->getData()->willReturn('2016-09-12');

        $this->beConstructedWith([$value1, $value2, $value3, $value4, $value5, $value6, $value7]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RequiredValueCollection::class);
    }

    function it_count_values()
    {
        $this->count()->shouldReturn(7);
    }

    function it_provides_an_iterator()
    {
        $this->getIterator()->shouldReturnAnInstanceOf('\ArrayIterator');
    }

    function it_filters_by_channel_and_locale(
        $value1,
        $value2,
        $value4,
        $value6,
        $value7,
        $fr_FR,
        $en_US,
        ChannelInterface $ecommerce
    ) {
        $ecommerce->getCode()->willReturn('ecommerce');

        $filteredValues = $this->filterByChannelAndLocale($ecommerce, $fr_FR);

        $filteredValues->shouldHaveType(RequiredValueCollection::class);
        $filteredValues->count()->shouldReturn(5);
        $filteredValues->hasSame($value1)->shouldReturn(true);
        $filteredValues->hasSame($value2)->shouldReturn(true);
        $filteredValues->hasSame($value4)->shouldReturn(true);
        $filteredValues->hasSame($value6)->shouldReturn(true);
        $filteredValues->hasSame($value7)->shouldReturn(true);

        $filteredValues = $this->filterByChannelAndLocale($ecommerce, $en_US);

        $filteredValues->shouldHaveType(RequiredValueCollection::class);
        $filteredValues->count()->shouldReturn(4);
        $filteredValues->hasSame($value1)->shouldReturn(true);
        $filteredValues->hasSame($value2)->shouldReturn(true);
        $filteredValues->hasSame($value6)->shouldReturn(true);
        $filteredValues->hasSame($value7)->shouldReturn(false);
    }
}
