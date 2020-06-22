<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionAssetNotFoundException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\ExecuteNamingConventionValidationException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConvention;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExecuteNamingConventionSpec extends ObjectBehavior
{
    function let(
        AssetRepositoryInterface $assetRepository,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violations,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier,
        Asset $asset,
        EditAssetCommand $editAssetCommand
    ) {
        $this->beConstructedWith(
            $assetRepository,
            $editAssetCommandFactory,
            $editAssetHandler,
            $validator
        );

        $assetRepository
            ->getByIdentifier($assetIdentifier)
            ->willReturn($asset);
        $assetFamilyIdentifier
            ->__toString()
            ->willReturn('packshot');
        $asset
            ->normalize()
            ->willReturn([
                'code' => 'packshot_1',
                'values' => [],
            ]);
        $editAssetCommandFactory
            ->create([
                'asset_family_identifier' => 'packshot',
                'code' => 'packshot_1',
                'values' => [],
            ])
            ->willReturn($editAssetCommand);
        $validator
            ->validate($editAssetCommand)
            ->willReturn($violations);
        $violations
            ->count()
            ->willReturn(0);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ExecuteNamingConvention::class);
    }

    function it_create_and_handle_an_asset_command(
        EditAssetHandler $editAssetHandler,
        EditAssetCommand $editAssetCommand,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier
    ) {
        $editAssetHandler
            ->__invoke($editAssetCommand)
            ->shouldBeCalled();

        $this->executeOnAsset($assetFamilyIdentifier, $assetIdentifier);
    }

    function it_throws_an_exception_if_the_asset_does_not_exists(
        AssetRepositoryInterface $assetRepository,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier
    ) {
        $assetRepository
            ->getByIdentifier($assetIdentifier)
            ->willThrow(AssetNotFoundException::class);

        $this
            ->shouldThrow(ExecuteNamingConventionAssetNotFoundException::class)
            ->during('executeOnAsset', [
                $assetFamilyIdentifier,
                $assetIdentifier,
            ]);
    }

    function it_throws_an_exception_if_the_asset_command_factory_fails(
        EditAssetCommandFactory $editAssetCommandFactory,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier
    ) {
        $editAssetCommandFactory
            ->create([
                'asset_family_identifier' => 'packshot',
                'code' => 'packshot_1',
                'values' => [],
            ])
            ->willThrow(NamingConventionException::class);

        $this
            ->shouldThrow(ExecuteNamingConventionException::class)
            ->during('executeOnAsset', [
                $assetFamilyIdentifier,
                $assetIdentifier,
            ]);
    }

    function it_throws_an_exception_if_the_command_is_not_valid(
        ValidatorInterface $validator,
        EditAssetCommand $editAssetCommand,
        ConstraintViolationListInterface $violations,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier
    ) {
        $validator
            ->validate($editAssetCommand)
            ->willReturn($violations);
        $violations
            ->count()
            ->willReturn(1);

        $this
            ->shouldThrow(ExecuteNamingConventionValidationException::class)
            ->during('executeOnAsset', [
                $assetFamilyIdentifier,
                $assetIdentifier,
            ]);
    }

    function it_throws_an_exception_if_the_handler_fails(
        EditAssetHandler $editAssetHandler,
        EditAssetCommand $editAssetCommand,
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetIdentifier $assetIdentifier
    ) {
        $editAssetHandler
            ->__invoke($editAssetCommand)
            ->willThrow(\Exception::class);

        $this
            ->shouldThrow(ExecuteNamingConventionException::class)
            ->during('executeOnAsset', [
                $assetFamilyIdentifier,
                $assetIdentifier,
            ]);
    }
}
