<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily\DeleteAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\DeleteAssetFamily\DeleteAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteAssetFamilyContext implements Context
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private DeleteAssetFamilyHandler $deleteAssetFamilyHandler;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private ValidatorInterface $validator;
    private ConstraintViolationsContext $constraintViolationsContext;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        DeleteAssetFamilyHandler $deleteAssetFamilyHandler,
        AssetFamilyExistsInterface $assetFamilyExists,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->deleteAssetFamilyHandler = $deleteAssetFamilyHandler;
        $this->assetFamilyExists = $assetFamilyExists;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->validator = $validator;
    }

    /**
     * @When /^the user deletes the asset family "([^"]+)"$/
     */
    public function theUserDeletesAssetFamily(string $identifier)
    {
        $command = new DeleteAssetFamilyCommand($identifier);

        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->deleteAssetFamilyHandler)($command);
        }
    }

    /**
     * @Then /^there should be no asset family "([^"]+)"$/
     */
    public function thereShouldBeNoAssetFamily(string $identifier)
    {
        Assert::false($this->assetFamilyExists->withIdentifier(
            AssetFamilyIdentifier::fromString($identifier)
        ));
    }
}
