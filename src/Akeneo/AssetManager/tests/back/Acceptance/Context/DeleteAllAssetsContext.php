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

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAllAssets\DeleteAllAssetFamilyAssetsHandler;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteAllAssetsContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER_FIRST = 'designer';
    private const ASSET_FAMILY_IDENTIFIER_SECOND = 'brand';

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var DeleteAllAssetFamilyAssetsHandler */
    private $deleteAllAssetsHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        DeleteAllAssetFamilyAssetsHandler $deleteAllAssetsHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $violationsContext,
        ExceptionContext $exceptionContext,
        CreateAssetFamilyHandler $createAssetFamilyHandler
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetRepository = $assetRepository;
        $this->deleteAllAssetsHandler = $deleteAllAssetsHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
    }

    /**
     * @Given /^two asset families with two assets each$/
     * @throws \Exception
     */
    public function twoAssetFamiliesWithTwoAssetsEach()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER_FIRST);
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER_FIRST);
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER_FIRST);

        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER_SECOND);
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER_SECOND);
        $this->createAsset(self::ASSET_FAMILY_IDENTIFIER_SECOND);
    }

    /**
     * @When /^the user deletes all the assets from one asset family$/
     */
    public function theUserDeletesAllTheAssetFromOneEntity(): void
    {
        $command = new DeleteAllAssetFamilyAssetsCommand(
            self::ASSET_FAMILY_IDENTIFIER_FIRST
        );

        $this->executeCommand($command);
    }

    /**
     * @When /^the user deletes all the assets from an unknown entity$/
     */
    public function theUserDeletesAllTheAssetFromUnknownEntity(): void
    {
        $command = new DeleteAllAssetFamilyAssetsCommand(
            'unknown'
        );

        $this->executeCommand($command);
    }

    /**
     * @When /^there should be no assets for this asset family$/
     */
    public function thereShouldBeNoAssetForThisEntity(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER_FIRST);
        Assert::assertFalse($this->assetRepository->assetFamilyHasAssets($assetFamilyIdentifier));
    }

    /**
     * @When /^there is still two assets on the other asset family$/
     */
    public function thereIsStillTwoAssetsForTheOtherEntity(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER_SECOND);
        Assert::assertEquals(2, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));
    }

    /**
     * @When /^there is still two assets for each asset family$/
     */
    public function thereIsStillTwoAssetsForEachEntity(): void
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER_FIRST);
        Assert::assertEquals(2, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER_SECOND);
        Assert::assertEquals(2, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));
    }

    private function createAssetFamily(string $identifier): void
    {
        $createCommand = new CreateAssetFamilyCommand(
            $identifier,
            []
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    private function createAsset(string $assetFamilyIdentifier): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString(str_replace('-', '', Uuid::uuid4()->toString()));
        $assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $this->assetRepository->create(Asset::create(
            $assetIdentifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        ));
    }

    private function executeCommand(DeleteAllAssetFamilyAssetsCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->deleteAllAssetsHandler)($command);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
