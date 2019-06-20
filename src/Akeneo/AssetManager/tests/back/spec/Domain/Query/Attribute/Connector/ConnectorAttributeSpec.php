<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use PhpSpec\ObjectBehavior;

class ConnectorAttributeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            AttributeCode::fromString('description'),
            LabelCollection::fromArray([
                'en_US' => 'Description',
                'fr_FR' => 'Description'
            ]),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(false),
            [
                'max_characters' => 123,
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => null,
                'validation_regexp' => null
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConnectorAttribute::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'code' => 'description',
            'labels'                   => [
                'en_US' => 'Description',
                'fr_FR' => 'Description',
            ],
            'type' => 'text',
            'value_per_locale' => true,
            'value_per_channel' => true,
            'is_required_for_completeness' => false,
            'max_characters' => 123,
            'is_textarea' => false,
            'is_rich_text_editor' => false,
            'validation_rule' => null,
            'validation_regexp' => null
        ]);
    }

    function it_maps_attribute_types()
    {
        $this->beConstructedWith(
            AttributeCode::fromString('country'),
            LabelCollection::fromArray([
                'en_US' => 'Country',
                'fr_FR' => 'Pays'
            ]),
            'record',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeIsRequired::fromBoolean(false),
            [
                "reference_entity_code" => 'country'
            ]
        );

        $this->normalize()->shouldReturn([
            'code' => 'country',
            'labels' => [
                'en_US' => 'Country',
                'fr_FR' => 'Pays'
            ],
            'type' => 'reference_entity_single_link',
            'value_per_locale' => true,
            'value_per_channel' => true,
            'is_required_for_completeness' => false,
            'reference_entity_code' => 'country'
        ]);
    }
}
