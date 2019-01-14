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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionCollectionAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Couleur', 'en_US' => 'Color']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true)
        ]);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
                'identifier'                  => 'name_designer_test',
                'reference_entity_identifier' => 'designer',
                'code'                        => 'name',
                'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => true,
                'value_per_locale'            => true,
                'type'                        => 'option_collection',
                'options'            => [],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OptionCollectionAttribute::class);
    }

    function it_can_have_a_multiple_options_set_to_it()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );
        $subject = $this->normalize();
        $subject->shouldReturn([
            'identifier'                  => 'name_designer_test',
            'reference_entity_identifier' => 'designer',
            'code'                        => 'name',
            'labels'                      => ['fr_FR' => 'Couleur', 'en_US' => 'Color'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => true,
            'value_per_locale'            => true,
            'type'                        => 'option_collection',
            'options'           => [
                [
                    'code' => 'red',
                    'labels'      => [
                        'fr_FR' => 'rouge',
                    ],
                ],
                [
                    'code' => 'green',
                    'labels'      => [
                        'fr_FR' => 'vert',
                    ],
                ],
            ],
        ]);
    }

    function it_cannot_have_more_too_options()
    {
        for ($i = 0; $i < 101; $i++) {
            $tooManyOptions[] = AttributeOption::create(
                OptionCode::fromString((string) $i),
                LabelCollection::fromArray([])
            );
        }
        $this->shouldThrow(\InvalidArgumentException::class)->during('setOptions', [$tooManyOptions]);
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

    function it_determines_if_a_given_option_code_exists_in_the_collection()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );

        $this->hasAttributeOption(OptionCode::fromString('red'))->shouldReturn(true);
        $this->hasAttributeOption(OptionCode::fromString('green'))->shouldReturn(true);
        $this->hasAttributeOption(OptionCode::fromString('pink'))->shouldReturn(false);
    }

    function it_gets_an_option_in_the_collection()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );

        $this->getAttributeOption(OptionCode::fromString('red'))->shouldBeLike(
            AttributeOption::create(
                OptionCode::fromString('red'),
                LabelCollection::fromArray(['fr_FR' => 'rouge'])
            )
        );
    }

    function it_triggers_exception_when_the_code_does_not_exist()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );

        $this->shouldThrow(\InvalidArgumentException::class)->during('getAttributeOption', [OptionCode::fromString('blue')]);
    }


    function it_adds_a_new_option()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                )
            ]
        );

        $newOption = AttributeOption::create(
            OptionCode::fromString('green'),
            LabelCollection::fromArray(['fr_FR' => 'vert'])
        );

        $this->addOption($newOption);

        $this->getAttributeOptions()->shouldBeLike(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                ),
                AttributeOption::create(
                    OptionCode::fromString('green'),
                    LabelCollection::fromArray(['fr_FR' => 'vert'])
                ),
            ]
        );
    }

    function it_has_a_limit_on_the_number_of_allowed_option_when_adding_a_new_option()
    {
        $options = [];
        for ($i = 0; $i < 100; $i++) {
            $options[] = AttributeOption::create(
                OptionCode::fromString((string) $i),
                LabelCollection::fromArray([])
            );
        }
        $this->setOptions($options);
        $newOption = AttributeOption::create(
            OptionCode::fromString('green'),
            LabelCollection::fromArray(['fr_FR' => 'vert'])
        );

        $this->shouldThrow(\InvalidArgumentException::class)->during('addOption', [$newOption]);
    }

    function it_cannot_add_a_new_option_if_the_option_already_exists()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                )
            ]
        );

        $newOption = AttributeOption::create(
            OptionCode::fromString('red'),
            LabelCollection::fromArray(['fr_FR' => 'rouge'])
        );

        $this->shouldThrow(\InvalidArgumentException::class)->during('addOption', [$newOption]);
    }

    function it_updates_an_existing_option()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                )
            ]
        );

        $option = AttributeOption::create(
            OptionCode::fromString('red'),
            LabelCollection::fromArray(['en_US' => 'red'])
        );

        $this->updateOption($option);

        $this->getAttributeOptions()->shouldBeLike(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['en_US' => 'red'])
                )
            ]
        );
    }

    function it_cannot_update_an_new_option_if_the_option_does_not_exist()
    {
        $this->setOptions(
            [
                AttributeOption::create(
                    OptionCode::fromString('red'),
                    LabelCollection::fromArray(['fr_FR' => 'rouge'])
                )
            ]
        );

        $newOption = AttributeOption::create(
            OptionCode::fromString('blue'),
            LabelCollection::fromArray([])
        );

        $this->shouldThrow(\InvalidArgumentException::class)->during('updateOption', [$newOption]);
    }
}
