<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsDecimal;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxValue;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMinValue;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                ReferenceEntityIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeIsDecimal::fromBoolean(false),
                AttributeMinValue::fromString('10'),
                AttributeMaxValue::fromString('20')
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NumberAttribute::class);
    }

    function it_determines_if_it_has_a_given_order()
    {
        $this->hasOrder(AttributeOrder::fromInteger(0))->shouldReturn(true);
        $this->hasOrder(AttributeOrder::fromInteger(1))->shouldReturn(false);
    }

    function it_creates_a_decimal_number()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                ReferenceEntityIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeIsDecimal::fromBoolean(true),
                AttributeMinValue::fromString('10'),
                AttributeMaxValue::fromString('20')
            ]
        );

        $this->normalize()['is_decimal']->shouldReturn(true);
    }

    function it_creates_a_non_decimal_number()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                ReferenceEntityIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeIsDecimal::fromBoolean(false),
                AttributeMinValue::fromString('10'),
                AttributeMaxValue::fromString('20')
            ]
        );
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'identifier'                  => 'area_city_test',
                'reference_entity_identifier' => 'city',
                'code'                        => 'area',
                'labels'                      => ['fr_FR' => 'Superficie', 'en_US' => 'Area'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'number',
                'is_decimal'                  => false,
                'min_value'                         => '10',
                'max_value'                         => '20'
            ]
        );
    }

    function it_can_have_its_is_decimal_flag_updated()
    {
        $this->setIsDecimal(AttributeIsDecimal::fromBoolean(true));

        $this->normalize()['is_decimal']->shouldBe(true);
    }

    function it_can_have_its_min_value_updated()
    {
        $this->setMinValue(AttributeMinValue::fromString('99'));

        $this->normalize()['min_value']->shouldBe('99');
    }

    function it_can_have_its_max_value_updated()
    {
        $this->setMaxValue(AttributeMaxValue::fromString('99'));

        $this->normalize()['max_value']->shouldBe('99');
    }
}
