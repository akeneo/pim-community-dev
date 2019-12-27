<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Acceptance\Context;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class EditAssetFamilyNamingConventionContext implements Context
{
    /** @var EditAssetFamilyHandler */
    private $editAssetFamilyHandler;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        EditAssetFamilyHandler $editAssetFamilyHandler,
        ConstraintViolationsContext $constraintViolationsContext,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ValidatorInterface $validator
    ) {
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->validator = $validator;
    }

    /**
     * @When the user edits the family to set a valid naming convention
     */
    public function theUserEditsTheFamilyToAddAValidNamingConvention(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
            'pattern' => '/valid_pattern/',
            'strict' => true
        ]);
    }

    /**
     * @Then the family naming convention should be set
     */
    public function theFamilyShouldHaveANamingConvention(): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $assetFamily = $this->getAssetFamily('designer');
        $namingConvention = $assetFamily->getNamingConvention()->normalize();
        Assert::keyExists($namingConvention, 'source');
        Assert::keyExists($namingConvention, 'pattern');
        Assert::keyExists($namingConvention, 'strict');
    }

    private function getAssetFamily(string $familyIdentifier): AssetFamily
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString($familyIdentifier)
        );
        Assert::notNull($assetFamily, sprintf('Could not find asset family %s', $familyIdentifier));

        return $assetFamily;
    }

    private function editNamingConventionForAssetFamily(string $familyIdentifier, array $namingConvention): void
    {
        $command = new EditAssetFamilyCommand(
            $familyIdentifier,
            ['en_US' => sprintf('My updated label for %s', $familyIdentifier)],
            null,
            null,
            [],
            null,
            $namingConvention
        );
        $this->editAssetFamily($command);
    }

    private function editAssetFamily(EditAssetFamilyCommand $editCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editCommand));
        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editAssetFamilyHandler)($editCommand);
        }
    }
}
