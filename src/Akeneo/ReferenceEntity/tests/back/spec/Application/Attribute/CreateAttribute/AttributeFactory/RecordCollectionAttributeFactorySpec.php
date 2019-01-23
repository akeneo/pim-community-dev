<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\RecordCollectionAttributeFactory;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateRecordCollectionAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use PhpSpec\ObjectBehavior;

class RecordCollectionAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(
            new CreateRecordCollectionAttributeCommand(
                'designer',
                'brands',
                ['fr_FR' => 'Marques'],
                true,
                false,
                false,
                'brand'
            )
        )->shouldReturn(true)
        ;
        $this->supports(
            new CreateImageAttributeCommand(
                'designer',
                'name',
                [
                    'fr_FR' => 'Nom',
                ],
                true,
                false,
                false,
                null,
                []
            )
        )->shouldReturn(false)
        ;
    }

    function it_creates_a_record_collection_attribute_with_a_command()
    {
        $command = new CreateRecordCollectionAttributeCommand(
            'designer',
            'brands',
            ['fr_FR' => 'Marques'],
            true,
            false,
            false,
            'brand'
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('brands_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn(
            [
                'identifier'                  => 'brands_designer_fingerprint',
                'reference_entity_identifier' => 'designer',
                'code'                        => 'brands',
                'labels'                      => ['fr_FR' => 'Marques'],
                'order'                       => 0,
                'is_required'                 => true,
                'value_per_channel'           => false,
                'value_per_locale'            => false,
                'type'                        => 'record_collection',
                'record_type'                 => 'brand',
            ]
        )
        ;
    }
}
