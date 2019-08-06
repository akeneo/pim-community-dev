<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CommandFactory\CreateAssetCollectionAttributeCommandFactory;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAssetCollectionAttributeCommand;
use PhpSpec\ObjectBehavior;

class CreateAssetCollectionAttributeCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAssetCollectionAttributeCommandFactory::class);
    }

    function it_only_supports_attribute_type_asset_collection()
    {
        $this->supports(['type' => 'asset_collection'])->shouldReturn(true);
        $this->supports(['type' => 'image'])->shouldReturn(false);
    }

    function it_creates_a_command_to_create_a_asset_collection_attribute()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'asset_type' => 'brand',
        ]);

        $command->shouldBeAnInstanceOf(CreateAssetCollectionAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('brands');
        $command->labels->shouldBeEqualTo(['fr_FR' => 'Marques']);
        $command->isRequired->shouldBeEqualTo(true);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
        $command->assetType->shouldBeEqualTo('brand');
    }

    function it_throws_an_exception_if_there_is_one_missing_common_property()
    {
        $command = [
            'asset_family_identifier' => 'designer',
            // 'code' => 'brands', // For the test purpose, this one is missing
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            'asset_type' => 'brand',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    function it_throws_an_exception_if_there_is_one_missing_additional_property()
    {
        $command = [
            'asset_family_identifier' => 'designer',
            'code' => 'brands',
            'labels' => ['fr_FR' => 'Marques'],
            'is_required' => true,
            'value_per_channel' => false,
            'value_per_locale' => false,
            // 'asset_type' => 'brand', // For the test purpose, this one is missing
        ];

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('create', [$command]);
    }

    public function it_builds_the_command_with_some_default_values()
    {
        $command = $this->create([
            'asset_family_identifier' => 'designer',
            'code' => 'brands',
            'asset_type' => 'brand',
        ]);

        $command->shouldBeAnInstanceOf(CreateAssetCollectionAttributeCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('brands');
        $command->assetType->shouldBeEqualTo('brand');

        // default values:
        $command->labels->shouldBeEqualTo([]);
        $command->isRequired->shouldBeEqualTo(false);
        $command->valuePerChannel->shouldBeEqualTo(false);
        $command->valuePerLocale->shouldBeEqualTo(false);
    }
}
