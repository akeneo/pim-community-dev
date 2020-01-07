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

use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyHandler;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateAttributeHandler;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaLinkAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
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

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    public function __construct(
        EditAssetFamilyHandler $editAssetFamilyHandler,
        ConstraintViolationsContext $constraintViolationsContext,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        ValidatorInterface $validator,
        CreateAttributeHandler $createAttributeHandler
    ) {
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->activatedLocales = $activatedLocales;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->validator = $validator;
        $this->createAttributeHandler = $createAttributeHandler;
    }

    /**
     * @Given /^an asset family with a naming convention$/
     */
    public function anAssetFamilyWithANamingConvention()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $createCommand = new CreateAssetFamilyCommand(
            'designer',
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ],
            [],
            null,
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null
                ],
                'pattern' => '/valid_pattern/',
                'strict' => true
            ]
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }

        ($this->createAssetFamilyHandler)($createCommand);
        $this->createTextAttribute('designer', 'title', false, false);
    }

    /**
     * @Given an asset family with a media link as attribute as main media
     */
    public function anAssetFamilyWithAMediaLinkAsAttributeAsMainMedia()
    {
        $this->anAssetFamilyWithANamingConvention();
        $this->createMediaLinkAttribute('designer', 'external_image', false, false);
        $editFamilyCommand = new EditAssetFamilyCommand('designer', [], null, 'external_image', null, null, null);
        $this->editAssetFamily($editFamilyCommand);
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
     * @When the user edits the family to set a naming convention with attribute as main media
     */
    public function theUserEditsTheFamilyToSetNamingConventionWithAttributeAsMainMedia(): void
    {
        $this->editNamingConventionForAssetFamily(
            'designer',
            [
                'source' => ['property' => 'attribute_as_main_media', 'channel' => null, 'locale' => null],
                'pattern' => '/valid_pattern/',
                'strict' => true
            ]
        );
    }

    /**
     * @When the user edits the family without naming convention
     */
    public function theUserEditsTheFamilyWithoutNamingConvention(): void
    {
        $this->editNamingConventionForAssetFamily('designer', null);
    }

    /**
     * @When the user edits the family naming convention with an invalid property
     */
    public function theUserEditsTheFamilyNamingConventionWithAnInvalidProperty(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'invalid_property', 'channel' => null, 'locale' => null],
            'pattern' => '/valid_pattern/',
            'strict' => true
        ]);
    }

    /**
     * @When the user edits the family naming convention with an empty source
     */
    public function theUserEditsTheFamilyNamingConventionWithAnEmptySource(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'pattern' => '/valid_pattern/',
            'strict' => true
        ]);
    }

    /**
     * @When the user edits the family naming convention with a localizable source
     */
    public function theUserEditsTheFamilyNamingConventionWithALocalizableSource(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'attribute_as_main_media', 'channel' => null, 'locale' => 'en_US'],
            'pattern' => '/valid_pattern/',
            'strict' => true
        ]);
    }

    /**
     * @When the user edits the family naming convention without pattern
     */
    public function theUserEditsTheFamilyNamingConventionWithoutPattern(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
            'strict' => true
        ]);
    }

    /**
     * @When the user edits the family naming convention with invalid pattern
     */
    public function theUserEditsTheFamilyNamingConventionWithInvalidPattern(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
            'pattern' => '/invalid)',
            'strict' => true
        ]);
    }

    /**
     * @When the user edits the family naming convention without strict
     */
    public function theUserEditsTheFamilyNamingConventionWithoutStrict(): void
    {
        $this->editNamingConventionForAssetFamily('designer', [
            'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
            'pattern' => '/valid_pattern/',
        ]);
    }

    /**
     * @Then there should be a validation error stating that the property is invalid
     */
    public function thereShouldBeAValidationErrorStatingThatAnAttributeIsInvalid()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'Invalid property "invalid_property". It should be either one of "code", "attribute_as_main_media".'
        );
    }

    /**
     * @Then there should be a validation error stating that the source must be defined
     */
    public function thereShouldBeAValidationErrorStatingThatTheSourceMustBeDefined()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'This field is missing.'
        );
    }

    /**
     * @Then there should be a validation error stating that the source must not be localizable
     */
    public function thereShouldBeAValidationErrorStatingThatTheSourceMustNotBeLocalizable()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'Attribute "media" is not localizable, you cannot define a locale'
        );
    }

    /**
     * @Then there should be a validation error stating that the pattern must be defined
     */
    public function thereShouldBeAValidationErrorStatingThatThePatternMustBeDefined()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'This field is missing.'
        );
    }

    /**
     * @Then there should be a validation error stating that the pattern is not valid
     */
    public function thereShouldBeAValidationErrorStatingThatThePatternIsNotValid()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'The regular expression "/invalid)" is malformed.'
        );
    }

    /**
     * @Then there should be a validation error stating that the strict must be defined
     */
    public function thereShouldBeAValidationErrorStatingThatTheStrictMustBeDefined()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'This field is missing.'
        );
    }

    /**
     * @Then there should be a validation error stating that the source cannot be the attribute as main media
     */
    public function thereShouldBeAValidationErrorStatingThatTheSourceCannotBeTheAttributeAsMainMedia()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'Cannot apply a naming convention on the attribute as main media if it is not a media file attribute.'
        );
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

    private function editNamingConventionForAssetFamily(string $familyIdentifier, ?array $namingConvention): void
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

    private function createTextAttribute(string $familyIdentifier, string $attributeCode, bool $scopable, bool $localizable): void
    {
        $createCommand = new CreateTextAttributeCommand(
            $familyIdentifier,
            $attributeCode,
            [],
            false,
            $scopable,
            $localizable,
            null,
            false,
            false,
            'none',
            null
        );
        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }
        ($this->createAttributeHandler)($createCommand);
    }

    private function createMediaLinkAttribute(
        string $familyIdentifier,
        string $attributeCode,
        bool $scopable,
        bool $localizable
    ): void {
        $createCommand = new CreateMediaLinkAttributeCommand(
            $familyIdentifier,
            $attributeCode,
            [],
            false,
            $scopable,
            $localizable,
            MediaType::IMAGE,
            null,
            null
        );
        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family attribute: %s', $violations->get(0)->getMessage()));
        }
        ($this->createAttributeHandler)($createCommand);
    }
}
