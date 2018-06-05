<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\IncompleteValueCollection;
use Pim\Component\Catalog\EntityWithFamily\RequiredValue;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class IncompleteValueCollectionSpec extends ObjectBehavior
{
    function let(
        AttributeInterface $length,
        AttributeInterface $price,
        AttributeInterface $description,
        AttributeInterface $releaseDate,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR,
        RequiredValue $value1,
        RequiredValue $value2,
        RequiredValue $value3,
        RequiredValue $value4,
        RequiredValue $value5,
        RequiredValue $value6
    ) {
        $length->isUnique()->willReturn(false);
        $price->isUnique()->willReturn(false);
        $description->isUnique()->willReturn(false);
        $releaseDate->isUnique()->willReturn(true);

        $length->isScopable()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $description->isScopable()->willReturn(true);
        $releaseDate->isScopable()->willReturn(false);

        $length->isLocalizable()->willReturn(false);
        $price->isLocalizable()->willReturn(false);
        $description->isLocalizable()->willReturn(true);
        $releaseDate->isLocalizable()->willReturn(false);

        $length->getCode()->willReturn('length');
        $price->getCode()->willReturn('price');
        $description->getCode()->willReturn('description');
        $ecommerce->getCode()->willReturn('ecommerce');
        $releaseDate->getCode()->willReturn('release_date');
        $print->getCode()->willReturn('print');
        $en_US->getCode()->willReturn('en_US');
        $fr_FR->getCode()->willReturn('fr_FR');

        $value1->forAttribute()->willReturn($length);
        $value2->forAttribute()->willReturn($price);
        $value3->forAttribute()->willReturn($description);
        $value4->forAttribute()->willReturn($description);
        $value5->forAttribute()->willReturn($description);
        $value6->forAttribute()->willReturn($releaseDate);

        $value1->forScope()->willReturn($ecommerce);
        $value2->forScope()->willReturn($ecommerce);
        $value3->forScope()->willReturn($ecommerce);
        $value4->forScope()->willReturn($print);
        $value5->forScope()->willReturn($print);
        $value6->forScope()->willReturn($print);

        $value1->forLocale()->willReturn($en_US);
        $value2->forLocale()->willReturn($en_US);
        $value3->forLocale()->willReturn($en_US);
        $value4->forLocale()->willReturn($fr_FR);
        $value5->forLocale()->willReturn($fr_FR);
        $value6->forLocale()->willReturn($fr_FR);

        $value1->attribute()->willReturn('length');
        $value2->attribute()->willReturn('price');
        $value3->attribute()->willReturn('description');
        $value4->attribute()->willReturn('description');
        $value5->attribute()->willReturn('description');
        $value6->attribute()->willReturn('release_date');

        $value1->scope()->willReturn(null);
        $value2->scope()->willReturn(null);
        $value3->scope()->willReturn('ecommerce');
        $value4->scope()->willReturn('ecommerce');
        $value5->scope()->willReturn('print');
        $value6->scope()->willReturn(null);

        $value1->locale()->willReturn(null);
        $value2->locale()->willReturn('en_US');
        $value3->locale()->willReturn('en_US');
        $value4->locale()->willReturn('fr_FR');
        $value5->locale()->willReturn('fr_FR');
        $value6->locale()->willReturn(null);

        $this->beConstructedWith([$value1, $value2, $value3, $value4, $value5, $value6]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IncompleteValueCollection::class);
    }

    function it_count_values()
    {
        $this->count()->shouldReturn(6);
    }

    function it_provides_an_iterator()
    {
        $this->getIterator()->shouldReturnAnInstanceOf('\ArrayIterator');
    }

    function it_returns_the_attributes($length, $price, $description, $releaseDate)
    {
        $attributes = $this->attributes();
        $attributes->count()->shouldReturn(4);
        $attributes->contains($length)->shouldReturn(true);
        $attributes->contains($price)->shouldReturn(true);
        $attributes->contains($description)->shouldReturn(true);
        $attributes->contains($releaseDate)->shouldReturn(true);
    }
}
