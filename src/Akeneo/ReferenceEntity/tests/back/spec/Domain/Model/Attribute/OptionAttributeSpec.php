<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'color', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray(['fr_FR' => 'Couleur', 'en_US' => 'Color']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionAttribute::class);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
                'identifier'                  => 'color_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                        => 'color',
                'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'option',
                'options'            => [],
            ]
        );
    }

    function it_can_have_an_option_set_to_it()
    {
        $this->setOptions([
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
            ]
        );
        $this->normalize()->shouldReturn([
            'identifier'                  => 'color_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                        => 'color',
            'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => true,
            'value_per_locale'            => true,
            'type'                        => 'option',
            'options'            => [
                [
                    'code' => 'red',
                    'labels'      => [
                        'fr_FR' => 'rouge',
                    ],
                ],
            ],
        ]);
    }

    function it_cannot_have_options_with_the_same_code()
    {
        $duplicates = [
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            ),
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray([])
            ),
        ];
        $this->shouldThrow(\InvalidArgumentException::class)->during('setOptions', [$duplicates]);
    }
}
