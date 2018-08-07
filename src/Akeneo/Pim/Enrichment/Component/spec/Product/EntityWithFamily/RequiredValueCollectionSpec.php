<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValue;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\RequiredValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;

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
        RequiredValue $value1,
        RequiredValue $value2,
        RequiredValue $value3,
        RequiredValue $value4,
        RequiredValue $value5,
        RequiredValue $value6,
        RequiredValue $value7
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

        $value1->forAttribute()->willReturn($length);
        $value2->forAttribute()->willReturn($price);
        $value3->forAttribute()->willReturn($description);
        $value4->forAttribute()->willReturn($description);
        $value5->forAttribute()->willReturn($description);
        $value6->forAttribute()->willReturn($releaseDate);
        $value7->forAttribute()->willReturn($image);

        $value1->forChannel()->willReturn($ecommerce);
        $value2->forChannel()->willReturn($ecommerce);
        $value3->forChannel()->willReturn($ecommerce);
        $value4->forChannel()->willReturn($ecommerce);
        $value5->forChannel()->willReturn($print);
        $value6->forChannel()->willReturn($print);
        $value7->forChannel()->willReturn($print);

        $value1->forLocale()->willReturn($fr_FR);
        $value2->forLocale()->willReturn($fr_FR);
        $value4->forLocale()->willReturn($fr_FR);
        $value3->forLocale()->willReturn($en_US);
        $value5->forLocale()->willReturn($en_US);
        $value6->forLocale()->willReturn($en_US);
        $value7->forLocale()->willReturn($en_US);

        $value1->attribute()->willReturn('length');
        $value2->attribute()->willReturn('price');
        $value3->attribute()->willReturn('description');
        $value4->attribute()->willReturn('description');
        $value5->attribute()->willReturn('description');
        $value6->attribute()->willReturn('release_date');
        $value7->attribute()->willReturn('image');

        $value1->channel()->willReturn(null);
        $value2->channel()->willReturn(null);
        $value3->channel()->willReturn('ecommerce');
        $value4->channel()->willReturn('ecommerce');
        $value5->channel()->willReturn('print');
        $value6->channel()->willReturn(null);
        $value7->channel()->willReturn(null);

        $value1->locale()->willReturn(null);
        $value2->locale()->willReturn(null);
        $value3->locale()->willReturn('en_US');
        $value4->locale()->willReturn('fr_FR');
        $value5->locale()->willReturn('en_US');
        $value6->locale()->willReturn(null);
        $value7->locale()->willReturn('fr_FR');

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
        $filteredValues->count()->shouldReturn(4);
        $filteredValues->hasSame($value1)->shouldReturn(true);
        $filteredValues->hasSame($value2)->shouldReturn(true);
        $filteredValues->hasSame($value4)->shouldReturn(true);
        $filteredValues->hasSame($value6)->shouldReturn(true);

        $filteredValues = $this->filterByChannelAndLocale($ecommerce, $en_US);

        $filteredValues->shouldHaveType(RequiredValueCollection::class);
        $filteredValues->count()->shouldReturn(4);
        $filteredValues->hasSame($value1)->shouldReturn(true);
        $filteredValues->hasSame($value2)->shouldReturn(true);
        $filteredValues->hasSame($value6)->shouldReturn(true);
        $filteredValues->hasSame($value7)->shouldReturn(false);
    }
}
