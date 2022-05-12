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
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateMediaFileAttributeCommand;
use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryClock;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class EditAssetFamilyTransformationsContext implements Context
{
    private const COMPLEX_TRANSFORMATIONS = [
        [
            'label' => 'label1',
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                ['type' => 'colorspace', 'parameters' => ['colorspace' => 'grey']],
                ['type' => 'optimize_jpeg', 'parameters' => ['quality' => 70]],
            ],
            'filename_prefix' => '1_',
            'filename_suffix' => '_3'
        ],
        [
            'label' => 'label_2',
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_scopable', 'channel' => 'ecommerce', 'locale' => null],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
            ],
            'filename_suffix' => '_4',
        ],
        [
            'label' => 'LABEL 3',
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_localizable', 'channel' => null, 'locale' => 'en_US'],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
            ],
            'filename_prefix' => '   ',
        ],
        [
            'label' => 'Label4',
            'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
            'target' => ['attribute' => 'target_scopable_localizable', 'channel' => 'ecommerce', 'locale' => 'en_US'],
            'operations' => [
                ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                ['type' => 'thumbnail', 'parameters' => ['width' => 100, 'height' => 80]],
            ],
            'filename_prefix' => null,
            'filename_suffix' => '   '
        ],
    ];

    private CreateAssetFamilyHandler $createAssetFamilyHandler;

    private EditAssetFamilyHandler $editAssetFamilyHandler;

    private CreateAttributeHandler $createAttributeHandler;

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ValidatorInterface $validator;

    private ConstraintViolationsContext $constraintViolationsContext;

    private InMemoryChannelExists $channelExists;

    private InMemoryClock $clock;

    public function __construct(
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        EditAssetFamilyHandler $editAssetFamilyHandler,
        CreateAttributeHandler $createAttributeHandler,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryChannelExists $channelExists,
        InMemoryClock $clock
    ) {
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->editAssetFamilyHandler = $editAssetFamilyHandler;
        $this->createAttributeHandler = $createAttributeHandler;
        $this->activatedLocales = $activatedLocales;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->channelExists = $channelExists;
        $this->clock = $clock;
    }

    /**
     * @Given an asset family :familyIdentifier with no transformation
     */
    public function anAssetFamilyWithNoTransformation(string $familyIdentifier): void
    {
        $this->createAssetFamily($familyIdentifier);
        $this->createMediaFileAttribute($familyIdentifier, 'main_image', false, false);
        $this->createMediaFileAttribute($familyIdentifier, 'target', false, false);
        $this->createMediaFileAttribute($familyIdentifier, 'target2', false, false);
        $this->createMediaFileAttribute($familyIdentifier, 'target_scopable', true, false);
        $this->createMediaFileAttribute($familyIdentifier, 'target_localizable', false, true);
        $this->createMediaFileAttribute($familyIdentifier, 'target_scopable_localizable', true, true);
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
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    [
                        'type' => 'scale',
                        'parameters' => ['ratio' => 75],
                    ],
                ],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add valid complex transformations
     */
    public function theUserEditsTheFamilyToAddValidComplexTransformations(string $familyIdentifier): void
    {
        InMemoryClock::$actualDateTime = new \DateTimeImmutable('2000-01-01');
        $this->editTransformationForAssetFamily($familyIdentifier, self::COMPLEX_TRANSFORMATIONS);
    }

    /**
     * @When the user edits the :familyIdentifier family to add a transformation with empty label
     */
    public function theUserEditsTheFamilyToAddATransformationWithEmptyLabel(string $familyIdentifier): void
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'label' => '',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ],
        ]);
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
                'label' => 'label',
                'source' => ['attribute' => 'unknown', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
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
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'unknown', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
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
                'label' => 'label1',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ],
            [
                'label' => 'label2',
                'source' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target2', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
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
                'label' => 'label1',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'scale', 'parameters' => ['ratio' => 75]],
                    ['type' => 'colorspace', 'parameters' => ['colorspace' => 'rgb']],
                    ['type' => 'scale', 'parameters' => ['ratio' => 80]],
                ],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add too many transformations
     */
    public function theUserEditsTheFamilyToAddToMuchTransformations(string $familyIdentifier)
    {
        $this->createChannel('print');
        $this->createLocale('fr_FR');
        $this->createLocale('en_GB');
        $transformations = [];
        $labelIndex = 0;
        foreach (['ecommerce', 'print'] as $scope) {
            $transformations[] = [
                'label' => sprintf('label_%d', $labelIndex++),
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target_scopable', 'channel' => $scope, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ];
            foreach (['fr_FR', 'en_US', 'en_GB'] as $locale) {
                $transformations[] = [
                    'label' => sprintf('label_%d', $labelIndex++),
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'target_localizable', 'channel' => null, 'locale' => $locale],
                    'operations' => [],
                    'filename_prefix' => '1_',
                    'filename_suffix' => '_2'
                ];
                $transformations[] = [
                    'label' => sprintf('label_%d', $labelIndex++),
                    'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                    'target' => ['attribute' => 'target_scopable_localizable', 'channel' => $scope, 'locale' => $locale],
                    'operations' => [],
                    'filename_prefix' => '1_',
                    'filename_suffix' => '_2'
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
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'unknown', 'parameters' => ['foo' => 'bar']],
                ],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
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
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'colorspace', 'parameters' => ['foo' => 'bar']],
                ],
                'filename_prefix' => '1_',
                'filename_suffix' => '_2'
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add transformations with same source and filename
     */
    public function theUserEditsTheFamilyToAddTransformationsWithSameSourceAndFilename(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'label' => 'label1',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
            ],
            [
                'label' => 'label2',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target2', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => '1_',
                'filename_suffix' => ''
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add transformations with invalid filename prefix
     */
    public function theUserEditsTheFamilyToAddTransformationsWithInvalidFilenamePrefix(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => ' % 1_',
                'filename_suffix' => '_2  '
            ],
        ]);
    }

    /**
     * @When the user edits the :familyIdentifier family to add transformations with invalid filename suffix
     */
    public function theUserEditsTheFamilyToAddTransformationsWithInvalidFilenameSuffix(string $familyIdentifier)
    {
        $this->editTransformationForAssetFamily($familyIdentifier, [
            [
                'label' => 'label',
                'source' => ['attribute' => 'main_image', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => ' 1_',
                'filename_suffix' => '_%2  '
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

        $expectedValue = array_map(
            function (array $transformation) {
                $transformation = array_filter($transformation);
                $transformation['updated_at'] = $this->clock->now()->format(\DateTimeImmutable::ISO8601);

                return $transformation;
            },
            self::COMPLEX_TRANSFORMATIONS
        );
        $value = $assetFamily->getTransformationCollection()->normalize();

        Assert::same($expectedValue, $value);
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
     * @Then there should be a validation error stating that the label is empty
     */
    public function thereShouldBeAValidationErrorStatingThatTheCodeIsInvalid()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'This value should not be blank.'
        );
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
            'The operation "scale" cannot be defined twice in a single transformation'
        );
    }

    /**
     * @Then there should be a validation error stating that the transformation limit is reached
     */
    public function thereShouldBeAValidationErrorStatingThatTheTransformationLimitIsReached()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'You have reached the limit of 10 transformations'
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
     * @Then there should be a validation error stating that operation is not instantiable
     */
    public function thereShouldBeAValidationErrorStatingThatOperationIsNotInstantiable()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            "The parameter 'colorspace' is required for the colorspace operation."
        );
    }

    /**
     * @Then there should be a validation error stating that filename is not unique
     */
    public function thereShouldBeAValidationErrorStatingThatFilenameIsNotUnique()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            'A transformation with filename\'s prefix "1_" and suffix "" already exists for attribute source "main_image"'
        );
    }

    /**
     * @Then there should be a validation error stating that filename prefix is not valid
     */
    public function thereShouldBeAValidationErrorStatingThatFilenamePrefixIsNotValid()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space."
        );
    }

    /**
     * @Then there should be a validation error stating that filename suffix is not valid
     */
    public function thereShouldBeAValidationErrorStatingThatFilenameSuffixIsNotValid()
    {
        $this->constraintViolationsContext->thereShouldBeAValidationErrorWithMessage(
            "Filename prefix contains illegal character. Allowed characters are alphanumerics, '_', '-', '.', and space."
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
            $transformations,
            null
        );
        $this->editAssetFamily($command);
    }

    private function createMediaFileAttribute(string $familyIdentifier, string $attributeCode, bool $scopable, bool $localizable): void
    {
        $createCommand = new CreateMediaFileAttributeCommand(
            $familyIdentifier,
            $attributeCode,
            [
                'en_US' => $attributeCode,
            ],
            false,
            false,
            $scopable,
            $localizable,
            null,
            [],
            MediaType::IMAGE
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
