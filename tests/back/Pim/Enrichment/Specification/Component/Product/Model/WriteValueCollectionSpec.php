<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class WriteValueCollectionSpec extends ObjectBehavior
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
        ValueInterface $value1,
        ValueInterface $value2,
        ValueInterface $value3,
        ValueInterface $value4,
        ValueInterface $value5,
        ValueInterface $value6
    ) {
        $length->isUnique()->willReturn(false);
        $price->isUnique()->willReturn(false);
        $description->isUnique()->willReturn(false);
        $releaseDate->isUnique()->willReturn(true);

        $length->getCode()->willReturn('length');
        $price->getCode()->willReturn('price');
        $description->getCode()->willReturn('description');
        $ecommerce->getCode()->willReturn('ecommerce');
        $releaseDate->getCode()->willReturn('release_date');
        $print->getCode()->willReturn('print');
        $en_US->getCode()->willReturn('en_US');
        $fr_FR->getCode()->willReturn('fr_FR');

        $value1->getAttributeCode()->willReturn('length');
        $value2->getAttributeCode()->willReturn('price');
        $value3->getAttributeCode()->willReturn('description');
        $value4->getAttributeCode()->willReturn('description');
        $value5->getAttributeCode()->willReturn('description');
        $value6->getAttributeCode()->willReturn('release_date');

        $value1->getScopeCode()->willReturn(null);
        $value2->getScopeCode()->willReturn(null);
        $value3->getScopeCode()->willReturn('ecommerce');
        $value4->getScopeCode()->willReturn('ecommerce');
        $value5->getScopeCode()->willReturn('print');
        $value6->getScopeCode()->willReturn(null);

        $value1->getLocaleCode()->willReturn(null);
        $value2->getLocaleCode()->willReturn(null);
        $value3->getLocaleCode()->willReturn('en_US');
        $value4->getLocaleCode()->willReturn('fr_FR');
        $value5->getLocaleCode()->willReturn('en_US');
        $value6->getLocaleCode()->willReturn(null);

        $value6->getData()->willReturn('2016-09-12');

        $this->beConstructedWith([$value1, $value2, $value3, $value4, $value5, $value6]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WriteValueCollection::class);
    }

    function it_creates_a_collection_from_another(
        WriteValueCollection $collection,
        ValueInterface $value,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('weight');
        $attribute->isUnique()->willReturn(false);

        $value->getAttributeCode()->willReturn('weight');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getLocaleCode()->willReturn('fr_FR');

        $collection->toArray()->willReturn([$value]);
        $this->beConstructedThrough('fromCollection', [$collection]);
        $this->count()->shouldReturn(1);
        $this->containsKey('weight-ecommerce-fr_FR')->shouldReturn(true);
    }

    function it_convert_the_collection_to_an_array($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );
    }

    function it_returns_the_first_value($value1)
    {
        $this->first()->shouldReturn($value1);
    }

    function it_returns_the_last_value($value6)
    {
        $this->last()->shouldReturn($value6);
    }

    function it_returns_the_key_of_the_current_value()
    {
        $this->key()->shouldReturn('length-<all_channels>-<all_locales>');
    }

    function it_returns_the_next_value($value2)
    {
        $this->next()->shouldReturn($value2);
    }

    function it_returns_the_current_value($value1)
    {
        $this->current()->shouldReturn($value1);
    }

    function it_removes_a_value_by_a_key_and_deletes_indexed_attribute(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        $value6
    ) {
        $this->removeKey('length-<all_channels>-<all_locales>')->shouldReturn($value1);

        $this->toArray()->shouldReturn(
            [
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['price', 'description', 'release_date']);
    }

    function it_removes_a_value_by_a_key_and_keeps_indexed_attribute(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        $value6
    ) {
        $this->removeKey('description-ecommerce-en_US')->shouldReturn($value3);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_does_not_removes_a_non_existing_key($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->removeKey('foo')->shouldReturn(null);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_removes_a_value_and_deletes_indexed_attribute($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->remove($value1)->shouldReturn(true);

        $this->toArray()->shouldReturn(
            [
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['price', 'description', 'release_date']);
    }

    function it_removes_a_value_and_keeps_indexed_attribute($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->remove($value3)->shouldReturn(true);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_does_not_removes_a_non_existing_value(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        $value6,
        ValueInterface $anotherValue
    ) {
        $this->remove($anotherValue)->shouldReturn(false);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_removes_a_value_by_attribute_and_deletes_indexed_attribute(
        $value2,
        $value3,
        $value4,
        $value5,
        $value6,
        $length
    ) {
        $this->removeByAttributeCode('length')->shouldReturn(true);

        $this->toArray()->shouldReturn(
            [
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['price', 'description', 'release_date']);
    }

    function it_does_not_removes_values_for_non_present_attribute(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        $value6
    ) {
        $this->removeByAttributeCode('another_attribute')->shouldReturn(false);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_contains_a_key()
    {
        $this->containsKey('nope')->shouldReturn(false);
        $this->containsKey('description-ecommerce-fr_FR')->shouldReturn(true);
    }

    function it_contains_a_value($value1, ValueInterface $anotherValue)
    {
        $this->contains($anotherValue)->shouldReturn(false);
        $this->contains($value1)->shouldReturn(true);
    }

    function it_gets_by_key($value4)
    {
        $this->getByKey('nope')->shouldReturn(null);
        $this->getByKey('description-ecommerce-fr_FR')->shouldReturn($value4);
    }

    function it_gets_by_codes($value4)
    {
        $this->getByCodes('nope')->shouldReturn(null);
        $this->getByCodes('nope', 'ecommerce', 'fr_FR')->shouldReturn(null);
        $this->getByCodes('description', 'nope', 'fr_FR')->shouldReturn(null);
        $this->getByCodes('description', 'ecommerce', 'nope')->shouldReturn(null);
        $this->getByCodes('description', 'ecommerce', 'fr_FR')->shouldReturn($value4);
    }

    function it_get_keys()
    {
        $this->getKeys()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>',
                'price-<all_channels>-<all_locales>',
                'description-ecommerce-en_US',
                'description-ecommerce-fr_FR',
                'description-print-en_US',
                'release_date-<all_channels>-<all_locales>',
            ]
        );
    }

    function it_get_values($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->getValues()->shouldReturn([$value1, $value2, $value3, $value4, $value5, $value6]);
    }

    function it_count_values()
    {
        $this->count()->shouldReturn(6);
    }

    function it_adds_new_value(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        $value6,
        ValueInterface $newValue,
        AttributeInterface $attribute
    ) {
        $attribute->isUnique()->willReturn(false);

        $newValue->getAttributeCode()->willReturn('weight');
        $newValue->getLocaleCode()->willReturn('en_US');
        $newValue->getScopeCode()->willReturn(null);
        $attribute->getCode()->willReturn('weight');

        $this->add($newValue)->shouldReturn(true);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
                'weight-<all_channels>-en_US' => $newValue,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date', 'weight']);
    }

    function it_adds_only_new_value($value1, $value2, $value3, $value4, $value5, $value6)
    {
        $this->add($value3)->shouldReturn(false);

        $this->toArray()->shouldReturn(
            [
                'length-<all_channels>-<all_locales>' => $value1,
                'price-<all_channels>-<all_locales>' => $value2,
                'description-ecommerce-en_US' => $value3,
                'description-ecommerce-fr_FR' => $value4,
                'description-print-en_US' => $value5,
                'release_date-<all_channels>-<all_locales>' => $value6,
            ]
        );

        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_checks_if_empty()
    {
        $this->isEmpty()->shouldReturn(false);

        $this->clear();
        $this->isEmpty()->shouldReturn(true);
    }

    function it_provides_an_iterator()
    {
        $this->getIterator()->shouldReturnAnInstanceOf('\ArrayIterator');
    }

    function it_clears_the_collection()
    {
        $this->clear();
        $this->toArray()->shouldReturn([]);
        $this->getAttributeCodes()->shouldReturn([]);
    }

    function it_gets_attribute_codes()
    {
        $this->getAttributeCodes()->shouldReturn(['length', 'price', 'description', 'release_date']);
    }

    function it_gets_values_keys()
    {
        $this->getKeys()->shouldReturn([
                'length-<all_channels>-<all_locales>',
                'price-<all_channels>-<all_locales>',
                'description-ecommerce-en_US',
                'description-ecommerce-fr_FR',
                'description-print-en_US',
                'release_date-<all_channels>-<all_locales>'
        ]);
    }

    function it_filters_values()
    {
        $filteredValues = $this->filter(
            function (ValueInterface $value) {
                return $value->getAttributeCode() === 'length';
            }
        );

        $filteredValues->shouldBeAnInstanceOf(WriteValueCollection::class);
        $filteredValues->count()->shouldReturn(1);
        $filteredValues->first()->getAttributeCode()->shouldReturn('length');
    }
}
