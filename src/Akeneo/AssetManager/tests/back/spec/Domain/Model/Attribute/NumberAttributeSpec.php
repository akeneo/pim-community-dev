<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
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
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString('10'),
                AttributeLimit::fromString('20')
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
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeDecimalsAllowed::fromBoolean(true),
                AttributeLimit::fromString('10'),
                AttributeLimit::fromString('20')
            ]
        );

        $this->normalize()['decimals_allowed']->shouldReturn(true);
    }

    function it_creates_a_non_decimal_number()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString('10'),
                AttributeLimit::fromString('20')
            ]
        );
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'identifier'                  => 'area_city_test',
                'asset_family_identifier' => 'city',
                'code'                        => 'area',
                'labels'                      => ['fr_FR' => 'Superficie', 'en_US' => 'Area'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'number',
                'decimals_allowed'                  => false,
                'min_value'                   => '10',
                'max_value'                   => '20'
            ]
        );
    }

    function it_cannot_be_created_with_a_min_greater_than_the_max()
    {
        $this->beConstructedThrough(
            'create',
            [
                AttributeIdentifier::create('city', 'area', 'test'),
                AssetFamilyIdentifier::fromString('city'),
                AttributeCode::fromString('area'),
                LabelCollection::fromArray(['fr_FR' => 'Superficie', 'en_US' => 'Area']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString('2'),
                AttributeLimit::fromString('1')
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_have_its_decimals_allowed_flag_updated()
    {
        $this->setDecimalsAllowed(AttributeDecimalsAllowed::fromBoolean(true));

        $this->normalize()['decimals_allowed']->shouldBe(true);
    }

    function it_can_have_its_min_and_max_value_updated_with_a_limit()
    {
        $this->setLimit(AttributeLimit::fromString('-1'), AttributeLimit::fromString('1'));

        $this->normalize()['min_value']->shouldBe('-1');
        $this->normalize()['max_value']->shouldBe('1');
    }

    function it_can_have_its_min_and_max_value_updated_with_no_limit()
    {
        $this->setLimit(AttributeLimit::limitless(), AttributeLimit::limitless());

        $this->normalize()['min_value']->shouldBe(null);
        $this->normalize()['max_value']->shouldBe(null);
    }

    function it_tells_if_its_min_or_min_are_limitless()
    {
        $this->setLimit(AttributeLimit::limitless(), AttributeLimit::limitless());
        $this->isMinLimitless()->shouldReturn(true);
        $this->isMaxLimitless()->shouldReturn(true);

        $this->setLimit(AttributeLimit::fromString('12'), AttributeLimit::fromString('12'));
        $this->isMinLimitless()->shouldReturn(false);
        $this->isMaxLimitless()->shouldReturn(false);
    }
}
