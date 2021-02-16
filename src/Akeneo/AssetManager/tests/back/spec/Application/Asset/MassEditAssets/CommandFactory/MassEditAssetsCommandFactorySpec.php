<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory\MassEditAssetsCommandFactory;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MassEditAssetsCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditAssetCommandFactory $editAssetCommandFactory
    ) {
        $this->beConstructedWith(
            $editAssetCommandFactory
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassEditAssetsCommandFactory::class);
    }

    function it_creates_a_mass_edit_command_from_normalized(
        $editAssetCommandFactory,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $query,
        EditTextValueCommand $textEditAssetValueCommand,
        EditOptionCollectionValueCommand $optionCollectionEditAssetValueCommand,
        EditAssetCommand $textEditAssetCommand,
        EditAssetCommand $optionCollectionEditAssetCommand
    ) {
        $assetFamilyIdentifier->__toString()->willReturn('packshot');
        $query->normalize()->willReturn([]);

        $updaters = [
            [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'set',
                'id' => 'some_uuid'
            ],
            [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'add',
                'id' => 'another_uuid'
            ]
        ];

        $textEditAssetCommand->beConstructedWith(['packshot', 'FAKE_CODE_FOR_MASS_EDIT_VALIDATION_0', [$textEditAssetValueCommand]]);

        $editAssetCommandFactory->create(Argument::any([
            'asset_family_identifier' => 'packshot',
            'values' => [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update'
            ]
        ]))->willReturn($textEditAssetCommand);

        $optionCollectionEditAssetCommand->beConstructedWith(['packshot', 'FAKE_CODE_FOR_MASS_EDIT_VALIDATION_1', [$optionCollectionEditAssetValueCommand]]);

        $editAssetCommandFactory->create(Argument::any([
            'asset_family_identifier' => 'packshot',
            'values' => [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options']
            ]
        ]))->willReturn($optionCollectionEditAssetCommand);

        $massEditAssetsCommand = $this->create($assetFamilyIdentifier, $query, $updaters);
        $massEditAssetsCommand->shouldBeAnInstanceOf(MassEditAssetsCommand::class);
    }

    function it_should_throw_if_updaters_are_not_valid(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $query
    ) {
        $updaters = [
            [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'set'
            ],
        ];

        $this->shouldThrow(new \InvalidArgumentException('Impossible to create a command of mass asset edition.'))->during('create', [$assetFamilyIdentifier, $query, $updaters]);
    }
}
