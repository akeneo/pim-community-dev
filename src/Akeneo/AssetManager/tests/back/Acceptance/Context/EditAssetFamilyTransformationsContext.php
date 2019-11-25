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
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateImageAttributeCommand;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationSpec;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class EditAssetFamilyTransformationsContext implements Context
{
    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var EditAssetFamilyHandler */
    private $editAssetFamilyHandler;

    /** @var CreateAttributeHandler */
    private $createAttributeHandler;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAttributeHandler $createAttributeHandler,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ValidatorInterface $validator
    ) {
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->activatedLocales = $activatedLocales;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->validator = $validator;
    }

    /**
     * @Given an asset family :familyIdentifier with no transformation
     */
    public function anAssetFamilyWithNoTransformation(string $familyIdentifier): void
    {
        $this->createAssetFamily($familyIdentifier);
        $this->createImageAttribute($familyIdentifier, 'main_image', false, false);
        $this->createImageAttribute($familyIdentifier, 'target', false, false);
    }

    /**
     * @Given an asset family :familyIdentifier with a transformation
     */
    public function anAssetFamilyWithATransformation(string $familyIdentifier): void
    {
        $this->anAssetFamilyWithNoTransformation($familyIdentifier);
        $this->theUserEditsTheFamilyToAddAValidTransformation($familyIdentifier);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a valid transformation
     */
    public function theUserEditsTheFamilyToAddAValidTransformation(string $familyIdentifier): void
    {
        $this->editAssetFamily(
            new EditAssetFamilyCommand(
                $familyIdentifier,
                [
                    'en_US' => ucfirst($familyIdentifier),
                ],
                null,
                [],
                [
                    [
                        'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                        'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                        'operations' => [
                            [
                                'type' => 'scale',
                                'parameters' => ['ratio' => 75],
                            ],
                        ],
                    ],
                ]
            )
        );
    }

    /**
     * @When the user edits the :familyIdentifier family to remove every transformation
     */
    public function theUserEditsTheFamilyToRemoveEveryTransformation(string $familyIdentifier): void
    {
        $this->editAssetFamily(
            new EditAssetFamilyCommand(
                $familyIdentifier,
                [
                    'en_US' => ucfirst($familyIdentifier),
                ],
                null,
                [],
                []
            )
        );
    }

    /**
     * @When the user edits the :familyIdentifier family without providing any transformation
     */
    public function theUserEditsTheFamilyWithoutProvidingAnyTransformation(string $familyIdentifier): void
    {
        $this->editAssetFamily(
            new EditAssetFamilyCommand(
                $familyIdentifier,
                [
                    'en_US' => sprintf('My updated label for %s', $familyIdentifier),
                ],
                null,
                [],
                null
            )
        );
    }

    /**
     * @Then the :familyIdentifier family should have a transformation
     */
    public function theFamilyShouldHaveATransformation(string $familyIdentifier): void
    {
        $assetFamily = $this->getAssetFamily($familyIdentifier);
        // TODO: there probably is a better way to test that
        Assert::count($assetFamily->getTransformationCollection()->normalize(), 1);
    }

    /**
     * @Then the :familyIdentifier family should not have any transformation
     */
    public function theFamilyShouldNotHaveAnyTransformation(string $familyIdentifier): void
    {
        $assetFamily = $this->getAssetFamily($familyIdentifier);
        Assert::eq($assetFamily->getTransformationCollection(), TransformationCollection::noTransformation());
    }

    private function createAssetFamily(string $familyIdentifier): void
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $createCommand = new CreateAssetFamilyCommand(
            $familyIdentifier,
            [
                'en_US' => ucfirst($familyIdentifier),
            ],
            [],
            []
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }
        ($this->createAssetFamilyHandler)($createCommand);
    }

    private function editAssetFamily(EditAssetFamilyCommand $editCommand): void
    {
        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(
                sprintf('Cannot edit transformations of asset family: %s', $violations->get(0)->getMessage())
            );
        }
        ($this->editAssetFamilyHandler)($editCommand);
    }

    private function createImageAttribute(string $familyIdentifier, string $attributeCode, bool $scopable, bool $localizable): void
    {
        $createCommand = new CreateImageAttributeCommand(
            $familyIdentifier,
            $attributeCode,
            [
                'en_US' => $attributeCode,
            ],
            false,
            $scopable,
            $localizable,
            null,
            []
        );
        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create asset family: %s', $violations->get(0)->getMessage()));
        }
        ($this->createAttributeHandler)($createCommand);
    }

    private function getAssetFamily(string $familyIdentifier): AssetFamily
    {
        $assetFamily = $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString($familyIdentifier)
        );
        Assert::notNull($assetFamily, sprintf('Could not find asset family %s', $familyIdentifier));

        return $assetFamily;
    }
}
