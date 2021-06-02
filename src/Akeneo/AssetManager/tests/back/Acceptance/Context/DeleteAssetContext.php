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

use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetHandler;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteAssetContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const ASSET_CODE = 'stark';

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AssetRepositoryInterface $assetRepository;

    private DeleteAssetHandler $deleteAssetHandler;

    private ValidatorInterface $validator;

    private ExceptionContext $exceptionContext;

    private ConstraintViolationsContext $violationsContext;

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        DeleteAssetHandler $deleteAssetHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $violationsContext,
        ExceptionContext $exceptionContext,
        CreateAssetFamilyHandler $createAssetFamilyHandler
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetRepository = $assetRepository;
        $this->deleteAssetHandler = $deleteAssetHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
    }

    /**
     * @Given /^an asset family with one asset$/
     * @throws \Exception
     */
    public function aAssetFamilyWithOneAsset()
    {
        $this->createAssetFamily();
        $this->createAsset();
    }

    /**
     * @When /^the user deletes the asset$/
     */
    public function theUserDeletesTheAsset(): void
    {
        $command = new DeleteAssetCommand(
            self::ASSET_CODE,
            self::ASSET_FAMILY_IDENTIFIER
        );

        $this->executeDeleteCommand($command);
    }

    /**
     * @When /^the user tries to delete asset that does not exist$/
     */
    public function theUserDeletesAWrongAsset(): void
    {
        $command = new DeleteAssetCommand(
            'unknown_code',
            self::ASSET_FAMILY_IDENTIFIER
        );

        $this->executeDeleteCommand($command);
    }

    /**
     * @Then /^the asset should not exist anymore$/
     */
    public function theAssetShouldNotExist()
    {
        try {
            $this->assetRepository->getByAssetFamilyAndCode(
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE)
            );
        } catch (AssetNotFoundException $exception) {
            return;
        }

        Assert::true(false, 'The asset should not exist');
    }

    private function createAssetFamily(): void
    {
        $createCommand = new CreateAssetFamilyCommand(
            self::ASSET_FAMILY_IDENTIFIER,
            [],
            [],
            []
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }

    private function createAsset(): void
    {
        $this->assetRepository->create(Asset::create(
            AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ASSET_CODE, self::FINGERPRINT),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE),
            ValueCollection::fromValues([])
        ));
    }

    private function executeDeleteCommand(DeleteAssetCommand $deleteAssetCommand): void
    {
        $violations = $this->validator->validate($deleteAssetCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->deleteAssetHandler)($deleteAssetCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
