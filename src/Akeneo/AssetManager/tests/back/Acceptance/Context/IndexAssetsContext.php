<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\Asset\IndexAssets\IndexAssetsByAssetFamilyCommand;
use Akeneo\AssetManager\Application\Asset\IndexAssets\IndexAssetsByAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\AssetIndexerSpy;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexAssetsContext implements Context
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private IndexAssetsByAssetFamilyHandler $indexAssetsByAssetFamily;

    private AssetIndexerSpy $assetIndexerSpy;

    private ConstraintViolationsContext $constraintViolationsContext;

    private ValidatorInterface $validator;

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        IndexAssetsByAssetFamilyHandler $indexAssetsByAssetFamily,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        AssetIndexerSpy $assetIndexerSpy,
        CreateAssetFamilyHandler $createAssetFamilyHandler
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->indexAssetsByAssetFamily = $indexAssetsByAssetFamily;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->assetIndexerSpy = $assetIndexerSpy;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
    }

    /**
     * @Given /^the asset family "([^"]*)"$/
     */
    public function theAssetFamily(string $assetFamilyIdentifier): void
    {
        $createCommand = new CreateAssetFamilyCommand($assetFamilyIdentifier, [], [], []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
    }
    /**
     * @Given /^none of the assets of "([^"]*)" are indexed$/
     */
    public function noneOfTheAssetsOfAreIndexed(string $assetFamilyIdentifier)
    {
        $this->assetIndexerSpy->assertAssetFamilyNotIndexed($assetFamilyIdentifier);
    }

    /**
     * @When /^the system administrator reindexes all the assets of "([^"]*)"$/
     */
    public function theSystemAdministratorReindexesAllTheAssetsOf(string $assetFamilyIdentifier): void
    {
        $command = new IndexAssetsByAssetFamilyCommand($assetFamilyIdentifier);
        $violations = $this->validator->validate($command);

        if (0 < $violations->count()) {
            $this->constraintViolationsContext->addViolations($violations);

            return;
        }
        ($this->indexAssetsByAssetFamily)($command);
    }

    /**
     * @Then /^the assets of the asset family "([^"]*)" have been indexed$/
     */
    public function theAssetsOfTheAssetFamilyHaveBeenIndexed(string $assetFamilyIdentifier): void
    {
        $this->assetIndexerSpy->assertAssetFamilyIndexed($assetFamilyIdentifier);
    }

    /**
     * @When /^the system administrator reindexes the assets of an asset family that does not exist$/
     */
    public function theSystemAdministratorReindexesTheAssetsOfAnAssetFamilyThatDoesNotExist()
    {
        $command = new IndexAssetsByAssetFamilyCommand('unknown_asset_family');
        $violations = $this->validator->validate($command);

        if (0 < $violations->count()) {
            $this->constraintViolationsContext->addViolations($violations);

            return;
        }
        ($this->indexAssetsByAssetFamily)($command);
    }
}
