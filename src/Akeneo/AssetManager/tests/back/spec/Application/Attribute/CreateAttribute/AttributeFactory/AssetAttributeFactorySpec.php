<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\AssetAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAssetAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use PhpSpec\ObjectBehavior;

class AssetAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetAttributeFactory::class);
    }

    function it_only_supports_create_asset_attribute_commands()
    {
        $this->supports(
            new CreateAssetAttributeCommand(
                'designer',
                'mentor',
                ['fr_FR' => 'Mentor'],
                false,
                false,
                false,
                false,
                'designer'
            )
        )->shouldReturn(true);
        $this->supports(
            new CreateMediaFileAttributeCommand(
                'designer',
                'name',
                [
                    'fr_FR' => 'Nom',
                ],
                true,
                true,
                false,
                false,
                null,
                [],
                MediaType::IMAGE
            )
        )->shouldReturn(false);
    }

    function it_creates_a_asset_attribute_with_a_command()
    {
        $command = new CreateAssetAttributeCommand(
            'designer',
            'mentor',
            ['fr_FR' => 'Mentor'],
            false,
            false,
            false,
            false,
            'designer'
        );

        $this->create(
            $command,
            AttributeIdentifier::fromString('mentor_designer_fingerprint'),
            AttributeOrder::fromInteger(0)
        )->normalize()->shouldReturn([
            'identifier'                  => 'mentor_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                        => 'mentor',
            'labels'                      => ['fr_FR' => 'Mentor'],
            'order'                       => 0,
            'is_required'                 => false,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'asset',
            'asset_type'                 => 'designer',
        ]);
    }
}
