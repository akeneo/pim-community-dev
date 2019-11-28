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
use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationSpec;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class EditAssetFamilyTransformationsContext implements Context
{
    private const COMPLEX_TRANSFORMATIONS = [
        [
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                ['type' => 'colorspace', 'parameters' => ['colorspace' => 'grey']],
            ],
        ],
        [
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_scopable', 'channel' => 'ecommerce', 'locale' => null],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
            ],
        ],
        [
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_localizable', 'channel' => null, 'locale' => 'en_US'],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
            ],
        ],
        [
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_scopable_localizable', 'channel' => 'ecommerce', 'locale' => 'en_US'],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                ['type' => 'thumbnail', 'parameters' => ['width' => 100, 'height' => 80]],
            ],
        ],
    ];

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

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var InMemoryChannelExists */
    private $channelExists;

    public function __construct(
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAttributeHandler $createAttributeHandler,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryChannelExists $channelExists
    ) {
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->activatedLocales = $activatedLocales;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->channelExists = $channelExists;
    }

    /**
     * @Given an asset family :familyIdentifier with no transformation
     */
    public function anAssetFamilyWithNoTransformation(string $familyIdentifier): void
    {
        $this->createAssetFamily($familyIdentifier);
        $this->createImageAttribute($familyIdentifier, 'main_image', false, false);
        $this->createImageAttribute($familyIdentifier, 'target', false, false);
        $this->createImageAttribute($familyIdentifier, 'target2', false, false);
        $this->createImageAttribute($familyIdentifier, 'target_scopable', true, false);
        $this->createImageAttribute($familyIdentifier, 'target_localizable', false, true);
        $this->createImageAttribute($familyIdentifier, 'target_scopable_localizable', true, true);
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
        $this->editTransformationForAssetFamily($familyIdentifier, [
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
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add valid complex transformations
     */
    public function theUserEditsTheFamilyToAddValidComplexTransformations(string $familyIdentifier): void
    {
        $this->editTransformationForAssetFamily($familyIdentifier, self::COMPLEX_TRANSFORMATIONS);
    }

    /**
     * @When the user edits the :familyIdentifier family to remove every transformation
     */
    public function theUserEditsTheFamilyToRemoveEveryTransformation(string $familyIdentifier): void
    {
        $this->editTransformationForAssetFamily($familyIdentifier, []);
    }

    /**
     * @When the user edits the :familyIdentifier family without providing any transformation
     */
    public function theUserEditsTheFamilyWithoutProvidingAnyTransformation(string $familyIdentifier): void
    {
        $this->editTransformationForAssetFamily($familyIdentifier, null);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with unknown source
     */
    public function theUserEditsTheFamilyToAddATransformationWithUnknownSource(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'unknown', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with unknown target
     */
    public function theUserEditsTheFamilyToAddATransformationWithUnknownTarget(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'unknown', 'channel' => null, 'locale' => null],
                'operations' => [],
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with source equal to a target
     */
    public function theUserEditsTheFamilyToAddATransformationWithSourceEqualToATarget(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
            ],
            [
                'source' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target2', 'channel' => null, 'locale' => null],
                'operations' => [],
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with duplicate operations
     */
    public function theUserEditsTheFamilyToAddATransformationWithDuplicateOperations(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                    ['type' => 'colorspace', 'parameters' => ['colorspace' => 'rgb']],
                    ['type' => 'scale', 'parameters' => ['ratio' => 80]],
                ],
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add too much transformations
     */
    public function theUserEditsTheFamilyToAddToMuchTransformations(string $familyIdentifier)
    {
        $this->createChannel('print');
        $this->createLocale('fr_FR');
        $this->createLocale('en_GB');
        $transformations = [];
        foreach (['ecommerce', 'print'] as $scope) {
            $transformations[] = [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target_scopable', 'channel' => $scope, 'locale' => null],
                'operations' => [],
            ];
            foreach (['fr_FR', 'en_US', 'en_GB'] as $locale) {
                $transformations[] = [
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'target_localizable', 'channel' => null, 'locale' => $locale],
                    'operations' => [],
                ];
                $transformations[] = [
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'target_scopable_localizable', 'channel' => $scope, 'locale' => $locale],
                    'operations' => [],
                ];
            }
        }

        $this->editTransformationForAssetFamily($familyIdentifier, $transformations);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with unknown operation
     */
    public function theUserEditsTheFamilyToAddATransformationWithUnknownOperation(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'unknown', 'parameters' => ['foo' => 'bar']],
                ],
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with wrong parameters for operation
     */
    public function theUserEditsTheFamilyToAddATransformationWithWrongParametersForOperation(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'colorspace', 'parameters' => ['foo' => 'bar']],
                ],
            ],
        ]);
    }

    /**
     * @Then the :familyIdentifier family should have :count transformation
     */
    public function theFamilyShouldHaveATransformation(string $familyIdentifier, int $count): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $assetFamily = $this->getAssetFamily($familyIdentifier);
        Assert::count($assetFamily->getTransformationCollection()->normalize(), $count);
    }

    /**
     * @Then the :familyIdentifier family should have the complex transformations
     */
    public function theFamilyShouldHaveTheComplexTransformations(string $familyIdentifier): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $assetFamily = $this->getAssetFamily($familyIdentifier);

        $value = self::COMPLEX_TRANSFORMATIONS;
        Assert::same(
            json_encode($assetFamily->getTransformationCollection()->normalize(), JSON_PRETTY_PRINT),
            json_encode($value, JSON_PRETTY_PRINT)
        );
    }

    /**
     * @Then the :familyIdentifier family should not have any transformation
     */
    public function theFamilyShouldNotHaveAnyTransformation(string $familyIdentifier): void
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();
        $assetFamily = $this->getAssetFamily($familyIdentifier);
        Assert::eq($assetFamily->getTransformationCollection(), TransformationCollection::noTransformation());
    }

    /**
     * @Then there should be a validation error stating that an attribute is not found
     */
    public function thereShouldBeAValidationErrorStatingThatAnAttributeIsNotFound()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'The attribute "unknown" does not exist for this asset family'
        );
    }

    /**
     * @Then there should be a validation error stating that the source is equal to the target
     */
    public function thereShouldBeAValidationErrorStatingThatTheSourceIsEqaulToTheTarget()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'The attribute source "target" can not be an attribute target of a transformation.'
        );
    }

    /**
     * @Then there should be a validation error stating that an operation is set twice
     */
    public function thereShouldBeAValidationErrorStatingThatAnOperationIsSetTwice()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'You cannot update the asset family "packshot" because the operation "operation_type" is specified twice in a single transformation'
        );
    }

    /**
     * @Then there should be a validation error stating that the transformation limit is reached
     */
    public function thereShouldBeAValidationErrorStatingThatTheTransformationLimitIsReached()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'You cannot update the asset family "packshot" because you have reached the limit of 10 transformations'
        );
    }

    /**
     * @Then there should be a validation error stating that an operation is unknown
     */
    public function thereShouldBeAValidationErrorStatingThatAnOperationIsUnknown()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'Operation "unknown" is unknown.'
        );
    }

    /**
     * @Then there should be a validation error stating that operation is not instanciable
     */
    public function thereShouldBeAValidationErrorStatingThatOperationIsNotInstanciable()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            "Key 'colorspace' must exist in parameters."
        );
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

        $this->constraintViolationsContext->addViolations($this->validator->validate($createCommand));
        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->createAssetFamilyHandler)($createCommand);
        }
    }

    private function editAssetFamily(EditAssetFamilyCommand $editCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editCommand));
        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editAssetFamilyHandler)($editCommand);
        }
    }

    private function editTransformationForAssetFamily(string $familyIdentifier, ?array $transformations): void
    {
        $command = new EditAssetFamilyCommand(
            $familyIdentifier,
            ['en_US' => sprintf('My updated label for %s', $familyIdentifier)],
            null,
            null,
            [],
            $transformations
        );
        $this->editAssetFamily($command);
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

    private function createChannel(string $channel): void
    {
        $this->channelExists->save(ChannelIdentifier::fromCode($channel));
    }

    private function createLocale(string $locale): void
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode($locale));
    }
}
