<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\AssetCollectionAttributeFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAssetCollectionAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use PhpSpec\ObjectBehavior;

class AssetCollectionAttributeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionAttributeFactory::class);
    }

    function it_only_supports_create_text_commands()
    {
        $this->supports(
            new CreateAssetCollectionAttributeCommand(
                'designer',
                'brands',
                ['fr_FR' => 'Marques'],
                true,
                false,
                false,
                'brand'
            )
        )->shouldReturn(true);
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
        )->shouldReturn(false);
    }

    function it_creates_a_asset_collection_attribute_with_a_command()
    {
        $command = new CreateAssetCollectionAttributeCommand(
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
        )->normalize()->shouldReturn([
            'identifier'                  => 'brands_designer_fingerprint',
            'asset_family_identifier' => 'designer',
            'code'                        => 'brands',
            'labels'                      => ['fr_FR' => 'Marques'],
            'order'                       => 0,
            'is_required'                 => true,
            'value_per_channel'           => false,
            'value_per_locale'            => false,
            'type'                        => 'asset_collection',
            'asset_type'                 => 'brand',
        ]);
    }
}
