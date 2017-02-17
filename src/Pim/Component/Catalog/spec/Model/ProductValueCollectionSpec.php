<?php

namespace spec\Pim\Component\Catalog\Model;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductValueInterface;

class ProductValueCollectionSpec extends ObjectBehavior
{
    function let(
        AttributeInterface $length,
        AttributeInterface $price,
        AttributeInterface $description,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR,
        ProductValueInterface $value1,
        ProductValueInterface $value2,
        ProductValueInterface $value3,
        ProductValueInterface $value4,
        ProductValueInterface $value5
    ) {
        $length->getCode()->willReturn('length');
        $price->getCode()->willReturn('price');
        $description->getCode()->willReturn('description');
        $ecommerce->getCode()->willReturn('ecommerce');
        $print->getCode()->willReturn('print');
        $en_US->getCode()->willReturn('en_US');
        $fr_FR->getCode()->willReturn('fr_FR');

        $value1->getAttribute()->willReturn($length);
        $value2->getAttribute()->willReturn($price);
        $value3->getAttribute()->willReturn($description);
        $value4->getAttribute()->willReturn($description);
        $value5->getAttribute()->willReturn($description);

        $value1->getScope()->willReturn(null);
        $value2->getScope()->willReturn(null);
        $value3->getScope()->willReturn('ecommerce');
        $value4->getScope()->willReturn('ecommerce');
        $value5->getScope()->willReturn('print');

        $value1->getLocale()->willReturn(null);
        $value2->getLocale()->willReturn(null);
        $value3->getLocale()->willReturn('en_US');
        $value4->getLocale()->willReturn('fr_FR');
        $value5->getLocale()->willReturn('en_US');

        $this->beConstructedWith([$value1, $value2, $value3, $value4, $value5]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueCollection::class);
    }

    function it_convert_the_collection_to_an_array($value1, $value2, $value3, $value4, $value5)
    {
        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);
    }

    function it_returns_the_first_value($value1)
    {
        $this->first()->shouldReturn($value1);
    }

    function it_returns_the_last_value($value5)
    {
        $this->last()->shouldReturn($value5);
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

    function it_removes_a_value_by_a_key_and_deletes_indexed_attribute($value1, $value2, $value3, $value4, $value5)
    {
        $this->removeKey('length-<all_channels>-<all_locales>')->shouldReturn($value1);

        $this->toArray()->shouldReturn([
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['price', 'description']);
    }

    function it_removes_a_value_by_a_key_and_keeps_indexed_attribute($value1, $value2, $value3, $value4, $value5)
    {
        $this->removeKey('description-ecommerce-en_US')->shouldReturn($value3);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
    }

    function it_does_not_removes_a_non_existing_key($value1, $value2, $value3, $value4, $value5)
    {
        $this->removeKey('foo')->shouldReturn(null);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
    }

    function it_removes_a_value_and_deletes_indexed_attribute($value1, $value2, $value3, $value4, $value5)
    {
        $this->remove($value1)->shouldReturn(true);

        $this->toArray()->shouldReturn([
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['price', 'description']);
    }

    function it_removes_a_value_and_keeps_indexed_attribute($value1, $value2, $value3, $value4, $value5)
    {
        $this->remove($value3)->shouldReturn(true);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
    }

    function it_does_not_removes_a_non_existing_value($value1, $value2, $value3, $value4, $value5, ProductValueInterface $anotherValue)
    {
        $this->remove($anotherValue)->shouldReturn(false);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
    }

    function it_contains_a_key()
    {
        $this->containsKey('nope')->shouldReturn(false);
        $this->containsKey('description-ecommerce-fr_FR')->shouldReturn(true);
    }

    function it_contains_a_value($value1, ProductValueInterface $anotherValue)
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
        $this->getKeys()->shouldReturn([
            'length-<all_channels>-<all_locales>',
            'price-<all_channels>-<all_locales>',
            'description-ecommerce-en_US',
            'description-ecommerce-fr_FR',
            'description-print-en_US'
        ]);
    }

    function it_get_values($value1, $value2, $value3, $value4, $value5)
    {
        $this->getValues()->shouldReturn([$value1, $value2, $value3, $value4, $value5]);
    }

    function it_count_values()
    {
        $this->count()->shouldReturn(5);
    }

    function it_adds_new_value(
        $value1,
        $value2,
        $value3,
        $value4,
        $value5,
        ProductValueInterface $newValue,
        AttributeInterface $attribute
    ) {
        $newValue->getAttribute()->willReturn($attribute);
        $newValue->getLocale()->willReturn('en_US');
        $newValue->getScope()->willReturn(null);
        $attribute->getCode()->willReturn('weight');

        $this->add($newValue)->shouldReturn(true);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5,
            'weight-<all_channels>-en_US' => $newValue
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description', 'weight']);
    }

    function it_adds_only_new_value($value1, $value2, $value3, $value4, $value5)
    {
        $this->add($value3)->shouldReturn(false);

        $this->toArray()->shouldReturn([
            'length-<all_channels>-<all_locales>' => $value1,
            'price-<all_channels>-<all_locales>' => $value2,
            'description-ecommerce-en_US' => $value3,
            'description-ecommerce-fr_FR' => $value4,
            'description-print-en_US' => $value5
        ]);

        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
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
        $this->getAttributesKeys()->shouldReturn([]);
    }

    function it_gets_attribute_keys()
    {
        $this->getAttributesKeys()->shouldReturn(['length', 'price', 'description']);
    }

    function it_gets_attributes($length, $price, $description)
    {
        $this->getAttributes()->shouldReturn([$length, $price, $description]);
    }
}
