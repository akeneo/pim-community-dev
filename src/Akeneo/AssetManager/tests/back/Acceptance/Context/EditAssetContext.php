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

namespace Akeneo\AssetManager\tests\back\Acceptance\Context;

use Akeneo\AssetManager\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\AssetManager\Acceptance\Context\ExceptionContext;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyHandler;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\NumberData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditAssetContext implements Context
{
    private const ASSET_FAMILY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const ASSET_CODE = 'stark';

    private const ECOMMERCE_CHANNEL_CODE = 'ecommerce';
    private const FRENCH_LOCALE_CODE = 'fr_FR';
    private const NOT_ACTIVATED_LOCALE_CODE = 'de_DE';

    private const DUMMY_IMAGE_FILEPATH = '/a/b/dummy_filename.png';
    private const DUMMY_IMAGE_FILENAME = 'dummy_filename.png';
    private const DUMMY_IMAGE_SIZE = 10;
    private const DUMMY_IMAGE_MIMETYPE = 'image/png';
    private const DUMMY_IMAGE_EXTENSION = 'png';

    private const TEXT_ATTRIBUTE_CODE = 'name';
    private const TEXT_ATTRIBUTE_IDENTIFIER = 'name_designer_fingerprint';
    private const IMAGE_ATTRIBUTE_CODE = 'primary_picture';
    private const IMAGE_ATTRIBUTE_IDENTIFIER = 'primary_picture_designer_fingerprint';
    private const ASSET_TYPE = 'brand';
    private const ASSET_ATTRIBUTE_CODE = 'brand_linked';
    private const ASSET_ATTRIBUTE_IDENTIFIER = 'brand_linked_designer_fingerprint';
    private const OPTION_ATTRIBUTE_CODE = 'favorite_color';
    private const OPTION_ATTRIBUTE_IDENTIFIER = 'favorite_color_designer_fingerprint';
    private const OPTION_COLLECTION_ATTRIBUTE_CODE = 'favorite_drinks';
    private const OPTION_COLLECTION_ATTRIBUTE_IDENTIFIER = 'favorite_drinks_designer_fingerprint';
    private const NUMBER_ATTRIBUTE_CODE = 'age';
    private const NUMBER_ATTRIBUTE_IDENTIFIER = 'age_designer_fingerprint';
    private const DUMMY_ORIGINAL_VALUE = 'Une valeur naïve';
    private const DUMMY_UPDATED_VALUE = 'An updated dummy data';

    private const DUMMY_FILEPATH_PREFIX = '/a/dummy/key';
    private const UPDATED_DUMMY_FILENAME = 'dummy_filename.png';

    private const INVALID_FILENAME = 144;
    private const INVALID_FILEPATH_VALUE = false;
    private const INVALID_IMAGE_MIMETYPE = 144;
    private const INVALID_IMAGE_SIZE = '1000 Ko';
    private const INVALID_IMAGE_EXTENSION = ['gif'];
    private const INTEGER_TOO_LONG = '99999999999999999999999999999999999999999999999999999999999999999999999999999999';

    private const INVALID_IMAGE_EXISTS = '/files/not_found.png';
    private const FILE_TOO_BIG = 'too_big.jpeg';
    private const FILE_TOO_BIG_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::FILE_TOO_BIG;
    private const UPDATED_DUMMY_FILE_FILEPATH = InMemoryFileStorer::FILES_PATH . self::UPDATED_DUMMY_FILENAME;
    private const WRONG_IMAGE_SIZE = 20000;
    private const WRONG_EXTENSION = 'gif';
    private const WRONG_EXTENSION_FILENAME = 'wrong_extension.gif';
    private const WRONG_EXTENSION_FILE_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::WRONG_EXTENSION_FILENAME;
    private const GOOD_EXTENSION_FILENAME = 'dummy_filename.png';
    private const GOOD_EXTENSION_FILE_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::GOOD_EXTENSION_FILENAME;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var EditAssetCommandFactory */
    private $editAssetCommandFactory;

    /** @var EditAssetHandler */
    private $editAssetHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var CreateAssetFamilyHandler */
    private $createAssetFamilyHandler;

    /** @var InMemoryFindFileDataByFileKey */
    private $findFileData;

    /** @var InMemoryFileExists */
    private $fileExists;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AssetRepositoryInterface $assetRepository,
        EditAssetCommandFactory $editAssetCommandFactory,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        CreateAssetFamilyHandler $createAssetFamilyHandler,
        InMemoryFindFileDataByFileKey $findFileData,
        InMemoryFileExists $fileExists
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetRepository = $assetRepository;
        $this->editAssetCommandFactory = $editAssetCommandFactory;
        $this->editAssetHandler = $editAssetHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->createAssetFamilyHandler = $createAssetFamilyHandler;
        $this->findFileData = $findFileData;
        $this->fileExists = $fileExists;
    }

    /**
     * @Given /^an asset family with a text attribute$/
     * @throws \Exception
     */
    public function anAssetFamilyWithATextAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value of "([^"]*)" for the text attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueOfFor(string $textData)
    {
        $textValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString($textData)
        );
        $this->createAsset($textValue);
    }

    /**
     * @When /^the user updates the text attribute of the asset to "([^"]*)"$/
     */
    public function theUserUpdatesTheTextOfOfTheAssetTo(string $newData): void
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $newData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Then /^the asset should have the text value "([^"]*)" for this attribute$/
     */
    public function theAssetShouldHaveTheTextValueFor(string $expectedValue): void
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValue, $value->getData()->normalize());
    }

    /**
     * @Given /^an asset family with an image attribute$/
     */
    public function anAssetFamilyWithAImageAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::noLimit(),
                AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED)
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with the file "([^"]*)" for the image attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithATheFileForTheImageAttribute(string $originalFilename)
    {
        $file = new FileInfo();
        $file->setOriginalFilename($originalFilename);
        $file->setKey(self::DUMMY_FILEPATH_PREFIX . $originalFilename);

        $this->fileExists->save($file->getKey());

        $fileValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::IMAGE_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($file, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
        );
        $this->createAsset($fileValue);
    }

    /**
     * @When /^the user updates the asset default image with a valid file$/
     */
    public function theUserUpdatesTheAssetDefaultImage()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();

        $fileData = $this->initUploadedFileData();

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => self::ASSET_CODE,
            'labels' => [],
            'values' => [
                [
                    'attribute' => $attributeAsMainMedia->normalize(),
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ]
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the asset default image with an empty image$/
     */
    public function theUserUpdatesTheAssetDefaultImageWithAnEmpty()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => self::ASSET_CODE,
            'values' => []
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the asset default image with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheAssetDefaultImageWithPathAndFilename(string $filePath, string $filename)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();

        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => self::ASSET_CODE,
            'values' => [
                [
                    'attribute' => $attributeAsMainMedia->normalize(),
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => $filename,
                        'filePath' => $filePath,
                    ],
                ],
            ]
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with a valid uploaded file$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetTo()
    {
        $fileData = $this->initUploadedFileData();
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the valid image for this attribute$/
     */
    public function theAssetShouldHaveTheImageForThisAttribute()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );
        Assert::notNull($value);
        $normalizeData = $value->getData()->normalize();
        Assert::keyExists($normalizeData, 'originalFilename');
        Assert::keyExists($normalizeData, 'filePath');
        Assert::same(self::UPDATED_DUMMY_FILENAME, $normalizeData['originalFilename']);
        Assert::same(self::UPDATED_DUMMY_FILE_FILEPATH, $normalizeData['filePath']);
    }

    /**
     * @Then /^there should be a validation error on the property text attribute with message "([^\']*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyTextAttributeWithMessage(string $expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::TEXT_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    /**
     * @Given /^an asset family with a text attribute with max length (\d+)$/
     * @throws \Exception
     */
    public function anAssetFamilyWithATextAttributeWithMaxLength(int $maxLength)
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(3),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger($maxLength),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset family with a text attribute with an email validation rule$/
     * @throws \Exception
     */
    public function anAssetFamilyWithATextAttributeWithAnEmailValidationRule()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset family with a text attribute with a regular expression validation rule like "([^"]*)"$/
     * @throws \Exception
     */
    public function anAssetFamilyWithATextAttributeWithARegularExpressionValidationRuleLike(
        string $regularExpression
    ): void {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
                AttributeRegularExpression::fromString($regularExpression)
            )
        );
    }

    /**
     * @When /^the user updates the text attribute of the asset to an invalid value type$/
     */
    public function theUserUpdatesTheTextAttributeOfTheAssetToAnInvalidValue()
    {
        try {
            $editCommand = $this->editAssetCommandFactory->create([
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                       => self::ASSET_CODE,
                'labels'                     => [],
                'values'                     => [
                    [
                        'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => 150,
                    ],
                ],
            ]);
            $this->executeCommand($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Given /^an asset family with a text attribute with an url validation rule$/
     * @throws \Exception
     */
    public function anAssetFamilyWithATextAttributeWithAnUrlValidationRule()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::fromString(AttributeValidationRule::URL),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user empties the text attribute of the asset$/
     */
    public function theUserEmptiesTheTextAttributeOfTheAsset()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => null,
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have an empty value for this attribute$/
     */
    public function theAssetShouldHaveAnEmptyValueForThisAttribute()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::null($value);
    }

    /**
     * @Given /^an asset family with a localizable attribute$/
     * @throws \Exception
     */
    public function anAssetFamilyWithALocalizableAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value for the french locale$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueForTheFrenchLocale()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE));

        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE)),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createAsset($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the asset for the french locale$/
     */
    public function theUserUpdatesTheAttributeOfTheAssetForTheFrenchLocale()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => self::FRENCH_LOCALE_CODE,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the localizable attribute value of the asset without specifying the locale$/
     */
    public function theUserUpdatesTheLocalizableAttributeValueOfTheAssetWithoutSpecifyingTheLocale()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an asset family with a not localizable attribute$/
     */
    public function anAssetFamilyWithANotLocalizableAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @When /^the user updates the not localizable attribute value of the asset by specifying the locale$/
     */
    public function theUserUpdatesTheNotLocalizableAttributeValueOfTheAssetBySpecifyingTheLocale()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => self::FRENCH_LOCALE_CODE,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the attribute value of the asset by specifying a not activated locale$/
     */
    public function theUserUpdatesTheAttributeValueOfTheAssetBySpecifyingANotActivatedLocale()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => self::NOT_ACTIVATED_LOCALE_CODE,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the attribute value of the asset by specifying a locale not activated for the ecommerce channel$/
     */
    public function theUserUpdatesTheAttributeValueOfTheAssetBySpecifyingALocaleNotActivatedForTheEcommerceChannel()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE));

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => self::ECOMMERCE_CHANNEL_CODE,
                    'locale'    => self::NOT_ACTIVATED_LOCALE_CODE,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the new default image$/
     */
    public function theAssetShouldHaveTheNewDefaultImage()
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString(self::ASSET_CODE)
        );

        $assetImage = $this->getImage(
            $asset->getValues()->normalize(),
            $attributeAsMainMedia->getIdentifier()->normalize()
        );
        Assert::false($assetImage === null);

        Assert::keyExists($assetImage, 'originalFilename');
        Assert::keyExists($assetImage, 'filePath');
        Assert::same(self::UPDATED_DUMMY_FILENAME, $assetImage['originalFilename']);
        Assert::same(self::UPDATED_DUMMY_FILE_FILEPATH, $assetImage['filePath']);
    }

    /**
     * @Given /^the asset should have an empty image$/
     */
    public function theAssetShouldHaveAnEmptyImage()
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->exceptionContext->assertThereIsNoExceptionThrown();

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsMainMedia = $assetFamily->getAttributeAsMainMediaReference();
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );

        $assetImage = $this->getImage(
            $asset->getValues()->normalize(),
            $attributeAsMainMedia->getIdentifier()->normalize()
        );
        Assert::true($assetImage === null);
    }

    /**
     * @Given /^the asset should have the updated value for this attribute and the french locale$/
     */
    public function theAssetShouldHaveTheUpdatedValueForThisAttributeAndTheFrenchLocale()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE))
            )
        );

        Assert::notNull($value);
        Assert::same(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @Given /^an asset family with a scopable attribute$/
     * @throws \Exception
     */
    public function anAssetFamilyWithAScopableAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value for the ecommerce channel$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueForTheEcommerceChannel()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));

        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
            LocaleReference::noReference(),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createAsset($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the asset for the ecommerce channel$/
     */
    public function theUserUpdatesTheAttributeOfTheAssetForTheEcommerceChannel()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => self::ECOMMERCE_CHANNEL_CODE,
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an asset family with a not scopable attribute$/
     */
    public function aAssetFamilyWithANotScopableAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value for the not scopable attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueForTheNotScopableAttribute()
    {
        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createAsset($localizedValue);
    }

    /**
     * @Given /^the asset should have the updated value for this attribute and the ecommerce channel$/
     */
    public function theAssetShouldHaveTheUpdatedValueForThisAttributeAndTheEcommerceChannel()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
                LocaleReference::noReference()
            )
        );
        Assert::notNull($value);
        Assert::same(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @Given /^an asset family with a scopable and localizable attribute$/
     */
    public function anAssetFamilyWithAScopableAndLocalizableAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(true),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(255),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value for the ecommerce channel and french locale$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueForTheEcommerceChannelAndFrenchLocale()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save(self::ECOMMERCE_CHANNEL_CODE, [self::FRENCH_LOCALE_CODE]);

        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE)),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createAsset($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the asset for the ecommerce channel and french locale$/
     */
    public function theUserUpdatesTheAttributeOfTheAssetForTheEcommerceChannelAndFrenchLocale()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => self::ECOMMERCE_CHANNEL_CODE,
                    'locale'    => self::FRENCH_LOCALE_CODE,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the updated value for this attribute and the ecommerce channel and the french locale$/
     */
    public function theAssetShouldHaveTheUpdatedValueForThisAttributeAndTheEcommerceChannelAndTheFrenchLocale()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE))
            )
        );
        Assert::notNull($value);
        Assert::same(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @When /^the user updates the attribute of the asset with an invalid channel$/
     */
    public function theUserUpdatesTheAttributeOfTheAssetForAnInvalidChannel()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => 155,
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user enriches a scopable attribute value of a asset without specifying the channel$/
     */
    public function theUserEnrichesAnScopableAttributeValueOfAAssetWithoutChannel()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the not scopable attribute of the asset by specifying a channel$/
     */
    public function theUserUpdatesTheNotScopableAttributeOfTheAssetBySpecifyingAChannel()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => self::ECOMMERCE_CHANNEL_CODE,
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the attribute value of the asset by specifying an unknown channel$/
     */
    public function theUserUpdatesTheAttributeValueOfTheAssetBySpecifyingAnUnknownChannel()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => 'unknown_channel',
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid uploaded file path$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidUploadedFilepath()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::UPDATED_DUMMY_FILENAME,
                        'filePath'         => self::INVALID_FILEPATH_VALUE
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid stored file path$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidStoredFilepath()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::DUMMY_IMAGE_FILENAME,
                        'filePath' => self::INVALID_FILEPATH_VALUE,
                        'size' => self::DUMMY_IMAGE_SIZE,
                        'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
                        'extension' => self::DUMMY_IMAGE_EXTENSION,
                        'updatedAt' => '2019-11-22T15:16:21+0000'
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid stored file size$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidStoredSize()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::INVALID_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::DUMMY_IMAGE_EXTENSION,
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid stored file extension$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidStoredExtension()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::DUMMY_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::INVALID_IMAGE_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid stored file mime type$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidStoredMimeType()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::DUMMY_IMAGE_SIZE,
            'mimeType' => self::INVALID_IMAGE_MIMETYPE,
            'extension' => self::DUMMY_IMAGE_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000'
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Then /^there should be a validation error on the default image with message "([^"]+)"$/
     */
    public function thereShouldBeAValidationErrorOnTheDefaultImageWithMessage(string $expectedMessage): void
    {
        $this->violationsContext->assertViolation($expectedMessage);
    }

    /**
     * @Then /^there should be a validation error on the property image attribute with message "(.*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyImageAttributeWithMessage(string $expectedMessage): void
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::IMAGE_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid uploaded file name$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidUploadedFileName()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::INVALID_FILENAME,
                        'filePath'         => self::FILE_TOO_BIG
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an image that does not exist$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnImageThatDoesNotExist()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::DUMMY_IMAGE_FILENAME,
                        'filePath' => self::INVALID_IMAGE_EXISTS,
                        'size' => self::DUMMY_IMAGE_SIZE,
                        'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
                        'extension' => self::DUMMY_IMAGE_EXTENSION,
                        'updatedAt' => '2019-11-22T15:16:21+0000',
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset to an invalid stored file name$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetToAnInvalidStoredFileName()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::INVALID_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::DUMMY_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::DUMMY_IMAGE_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with a bigger uploaded file than the limit$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithABiggerUploadedFileThanTheLimit()
    {
        $fileData = $this->initUploadedFileData([
            'size' => intval(10e6),
        ]);
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with a bigger stored file than the limit$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithABiggerStoredFileThanTheLimit()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::WRONG_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::DUMMY_IMAGE_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with a smaller file than the limit$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithASmallerFileThanTheLimit()
    {
        $fileData = $this->initUploadedFileData([
            'size' => 1,
        ]);
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an asset family with an image attribute having a max file size of 15ko$/
     */
    public function anAssetFamilyWithAnImageAttributeHavingAMaxFileSizeOf10k()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('0.015'),
                AttributeAllowedExtensions::fromList([])
            )
        );
    }

    /**
     * @When /^the user updates the image attribute of the asset with an uploaded gif file which is a denied extension$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithAnUploadedFileHavingADeniedExtension()
    {
        $fileData = $this->initUploadedFileData([
            'extension' => current(self::INVALID_IMAGE_EXTENSION),
        ]);
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with a stored gif file which is a denied extension$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithAnStoredFileHavingADeniedExtension()
    {
        $this->fileExists->save(self::DUMMY_IMAGE_FILEPATH);

        $fileData = [
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::DUMMY_IMAGE_FILEPATH,
            'size' => self::DUMMY_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::WRONG_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ];
        $this->findFileData->save($fileData);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the asset with an uploaded png file$/
     */
    public function theUserUpdatesTheImageAttributeOfTheAssetWithAnUploadedFileHavingAValidExtension()
    {
        $fileData = $this->initUploadedFileData();
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $fileData,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an asset family with an image attribute allowing only files with extension png$/
     */
    public function anAssetFamilyWithAnImageAttributeAllowingOnlyFilesWithExtensionJpeg()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('150.110'),
                AttributeAllowedExtensions::fromList(['png'])
            )
        );
    }

    /**
     * @When /^the user removes an image from the asset for this attribute$/
     */
    public function theUserRemovesAnImageFromTheAssetForThisAttribute()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => null,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should not have any image for this attribute$/
     */
    public function theAssetShouldNotHaveAnyImageForThisAttribute()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );
        Assert::null($value);
    }

    /**
     * @Given /^an asset family and a asset with french label "([^"]*)"$/
     */
    public function aAssetFamilyAndAAssetWithLabel(string $label): void
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $labelValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::ASSET_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString($label)
        );
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ASSET_CODE, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues([$labelValue])
            )
        );
    }

    /**
     * @Given /^a assetFamily and a asset with an image$/
     */
    public function aAssetFamilyAndAAssetWithAnImage(): void
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(self::DUMMY_IMAGE_FILENAME)
            ->setKey(self::DUMMY_IMAGE_FILEPATH);
        $labelValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::ASSET_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('fr_label')
        );

        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ASSET_CODE, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues([$labelValue])
            )
        );
    }

    /**
     * @When /^the user updates the french label to "([^"]*)"$/
     */
    public function theUserUpdatesTheLabelTo(string $updatedLabel)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();

        $editLabelCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                        => self::ASSET_CODE,
            'values'                      => [
                [
                    'attribute' => $attributeAsLabel->normalize(),
                    'channel'   => null,
                    'locale'    => 'fr_FR',
                    'data'      => $updatedLabel,
                ],
            ],
        ]);
        $this->executeCommand($editLabelCommand);
    }

    /**
     * @When /^the user empties the french label$/
     */
    public function theUserEmptiesTheLabel()
    {
        $editLabelCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [
                'fr_FR' => ''
            ],
            'values'                     => [],
        ]);
        $this->executeCommand($editLabelCommand);
    }

    /**
     * @Then /^the asset should have the french label "([^"]*)"$/
     */
    public function theAssetShouldHaveTheLabel(string $expectedLabel)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString(self::ASSET_CODE)
        );
        $actualLabels = $this->getLabelsFromValues(
            $asset->getValues()->normalize(),
            $attributeAsLabel->getIdentifier()->normalize()
        );
        Assert::same($expectedLabel, $actualLabels['fr_FR'], 'Labels are not equal');
    }

    /**
     * @Then /^the asset should not have a french label$/
     */
    public function theAssetShouldNotHaveLabel()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString(self::ASSET_CODE)
        );
        $actualLabels = $this->getLabelsFromValues(
            $asset->getValues()->normalize(),
            $attributeAsLabel->getIdentifier()->normalize()
        );
        Assert::IsEmpty($actualLabels, 'French label is not null');
    }

    /**
     * @When /^the user updates the german label to "([^"]*)"$/
     */
    public function theUserUpdatesTheGermanLabelTo(string $updatedLabel)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $attributeAsLabel = $assetFamily->getAttributeAsLabelReference();

        $editLabelCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                        => self::ASSET_CODE,
            'values'                      => [
                [
                    'attribute' => $attributeAsLabel->normalize(),
                    'channel'   => null,
                    'locale'    => 'de_DE',
                    'data'      => $updatedLabel,
                ],
            ],
        ]);
        $this->executeCommand($editLabelCommand);
    }

    /**
     * @Then /^there should be a validation error on the property labels with message "(.*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyLabelsWithMessage($expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage('values.label', $expectedMessage);
    }

    /**
     * @Then /^there should be (\d+) assets$/
     */
    public function thereShouldBeAssets(int $expectedCount)
    {
        $this->violationsContext->assertThereIsNoViolations();
        $this->violationsContext->assertThereIsNoViolations();
        $assetsCount = $this->assetRepository->count();
        Assert::same($expectedCount, $assetsCount);
    }

    private function createAssetFamily($assetFamilyIdentifier): void
    {
        $createCommand = new CreateAssetFamilyCommand(
            $assetFamilyIdentifier,
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

    private function createAsset(Value $value): void
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, self::ASSET_CODE, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AssetCode::fromString(self::ASSET_CODE),
                ValueCollection::fromValues([$value])
            )
        );
    }

    private function executeCommand(EditAssetCommand $editCommand): void
    {
        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->editAssetHandler)($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Given /^an asset family with a asset attribute$/
     */
    public function anAssetFamilyWithAAssetAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->createAssetFamily(self::ASSET_TYPE);
        $this->attributeRepository->create(
            AssetAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::ASSET_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::ASSET_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString(self::ASSET_TYPE)
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with a value of "([^"]*)" for the asset attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithAValueOfForTheAssetAttribute($assetCode)
    {
        $this->createAssetLinked($assetCode);

        $assetValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::ASSET_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            AssetData::createFromNormalize($assetCode)
        );
        $this->createAsset($assetValue);
    }

    /**
     * @When /^the user updates the asset attribute of the asset to "([^"]*)"$/
     */
    public function theUserUpdatesTheAssetAttributeOfTheAssetTo($assetCode)
    {
        $this->createAssetLinked($assetCode);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => $assetCode,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user tries to update the asset attribute of the asset with an unknown value$/
     */
    public function theUserTriesToUpdateTheAssetAttributeOfTheAssetWithAnUnknownValue()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => 'unknown_brand',
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Then /^the asset should have the asset value "([^"]*)" for this attribute$/
     */
    public function theAssetShouldHaveTheAssetValueForThisAttribute($expectedValue)
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::ASSET_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValue, $value->getData()->normalize());
    }

    /**
     * @When /^the user updates the asset attribute of the asset to an invalid asset value$/
     */
    public function theUserUpdatesTheAssetAttributeOfTheAssetToAnInvalidAssetValue()
    {
        try {
            $editCommand = $this->editAssetCommandFactory->create([
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                       => self::ASSET_CODE,
                'labels'                     => [],
                'values'                     => [
                    [
                        'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => 1,
                    ],
                ],
            ]);
            $this->executeCommand($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Then /^there should be a validation error on the property asset attribute with message "(.*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyAssetAttributeWithMessage($expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::ASSET_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with values of "([^"]*)" for the asset collection attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithValuesOfForTheAssetCollectionAttribute($assetCodeCollection)
    {
        $assetCodeCollection = explode(',', $assetCodeCollection);
        foreach ($assetCodeCollection as $assetCode) {
            $this->createAssetLinked(trim($assetCode));
        }

        $assetValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::ASSET_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            AssetCollectionData::createFromNormalize($assetCodeCollection)
        );
        $this->createAsset($assetValue);
    }

    /**
     * @When /^the user updates the asset collection attribute of the asset to "([^"]*)"$/
     */
    public function theUserUpdatesTheAssetCollectionAttributeOfTheAssetTo($assetCodeCollection)
    {
        $assetCodeCollection = explode(',', $assetCodeCollection);
        foreach ($assetCodeCollection as $assetCode) {
            $this->createAssetLinked(trim($assetCode));
        }

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => array_map(function ($newData) {
                        return trim($newData);
                    }, $assetCodeCollection),
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the asset collection attribute of the asset with unknown values$/
     */
    public function theUserUpdatesTheAssetCollectionAttributeOfTheAssetWithUnknownValues()
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code'                       => self::ASSET_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'unknown_brand',
                        'wrong_brand'
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Then /^the asset should have the asset collection value "([^"]*)" for this attribute$/
     */
    public function theAssetShouldHaveTheAssetCollectionValueForThisAttribute($expectedValue)
    {
        $expectedValue = explode(',', $expectedValue);
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::ASSET_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValue, $value->getData()->normalize());
    }

    private function createAssetLinked($assetCode)
    {
        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::create(self::ASSET_TYPE, $assetCode, self::FINGERPRINT),
                AssetFamilyIdentifier::fromString(self::ASSET_TYPE),
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([])
            )
        );
    }

    /**
     * @When /^the user updates the asset collection attribute of the asset to an invalid asset value$/
     */
    public function theUserUpdatesTheAssetCollectionAttributeOfTheAssetToAnInvalidAssetValue()
    {
        try {
            $editCommand = $this->editAssetCommandFactory->create([
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                       => self::ASSET_CODE,
                'labels'                     => [],
                'values'                     => [
                    [
                        'attribute' => self::ASSET_ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => 'invalid_asset_collection',
                    ],
                ],
            ]);
            $this->executeCommand($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }

    /**
     * @Given /^an asset family with a asset collection attribute$/
     */
    public function anAssetFamilyWithAAssetCollectionAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->createAssetFamily(self::ASSET_TYPE);
        $this->attributeRepository->create(
            AssetCollectionAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::ASSET_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::ASSET_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AssetFamilyIdentifier::fromString(self::ASSET_TYPE)
            )
        );
    }

    /**
     * @Given /^an asset family with an option attribute$/
     */
    public function aAssetFamilyWithAnOptionAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);

        $attribute = OptionAttribute::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::OPTION_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::OPTION_ATTRIBUTE_CODE),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $attribute->setOptions([
            AttributeOption::create(OptionCode::fromString('red'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('green'), LabelCollection::fromArray([])),
        ]);

        $this->attributeRepository->create($attribute);
    }

    /**
     * @Given /^an asset belonging to this asset family with values of "([^"]+)" for the option attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithValuesOfForTheOptionAttribute($optionCode)
    {
        $assetValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::OPTION_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionData::createFromNormalize($optionCode)
        );
        $this->createAsset($assetValue);
    }

    /**
     * @When /^the user updates the option attribute of the asset to "([^"]+)"$/
     */
    public function theUserUpdatesTheOptionAttributeOfTheAssetTo($optionCode)
    {
        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => self::ASSET_CODE,
            'labels' => [],
            'values' => [
                [
                    'attribute' => self::OPTION_ATTRIBUTE_IDENTIFIER,
                    'channel' => null,
                    'locale' => null,
                    'data' => $optionCode
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the option value "([^"]+)" for this attribute$/
     */
    public function theAssetShouldHaveTheOptionValueForThisAttribute($expectedValue)
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );

        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::OPTION_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValue, $value->getData()->normalize());
    }

    /**
     * @Then /^there should be a validation error on the property option attribute with message "(.*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyOptionAttributeWithMessageBlue($expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::OPTION_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    /**
     * @Given /^an asset family with an option collection attribute$/
     */
    public function aAssetFamilyWithAnOptionCollectionAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);

        $attribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::OPTION_COLLECTION_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString(self::OPTION_COLLECTION_ATTRIBUTE_CODE),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $attribute->setOptions([
            AttributeOption::create(OptionCode::fromString('vodka'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('rhum'), LabelCollection::fromArray([])),
            AttributeOption::create(OptionCode::fromString('whisky'), LabelCollection::fromArray([])),
        ]);

        $this->attributeRepository->create($attribute);
    }

    /**
     * @Given /^an asset belonging to this asset family with values of "([^"]+)" for the option collection attribute$/
     */
    public function aAssetBelongingToThisAssetFamilyWithValuesOfForTheOptionCollectionAttribute($optionCodes)
    {
        $optionCodesArray = explode(',', $optionCodes);
        $optionCodesArray = array_map('trim', $optionCodesArray);

        $assetValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::OPTION_COLLECTION_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            OptionCollectionData::createFromNormalize($optionCodesArray)
        );
        $this->createAsset($assetValue);
    }

    /**
     * @When /^the user updates the option collection attribute of the asset to "([^"]+)"$/
     */
    public function theUserUpdatesTheOptionCollectionAttributeOfTheAssetTo($optionCodes)
    {
        $optionCodesArray = explode(',', $optionCodes);
        $optionCodesArray = array_map('trim', $optionCodesArray);

        $editCommand = $this->editAssetCommandFactory->create([
            'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
            'code' => self::ASSET_CODE,
            'labels' => [],
            'values' => [
                [
                    'attribute' => self::OPTION_COLLECTION_ATTRIBUTE_IDENTIFIER,
                    'channel' => null,
                    'locale' => null,
                    'data' => $optionCodesArray
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the option collection value "([^"]+)" for this attribute$/
     */
    public function theAssetShouldHaveTheOptionCollectionValueForThisAttribute($expectedValue)
    {
        $expectedValueArray = explode(',', $expectedValue);
        $expectedValueArray = array_map('trim', $expectedValueArray);

        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );

        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::OPTION_COLLECTION_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValueArray, $value->getData()->normalize());
    }

    /**
     * @Then /^there should be a validation error on the property option collection attribute with message "(.*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyOptionCollectionAttributeWithMessage($expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::OPTION_COLLECTION_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    private function getLabelsFromValues(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    private function getImage(array $valueCollection, string $attributeAsMainMedia)
    {
        $emptyImage = null;

        $value = current(
            array_filter(
                $valueCollection,
                function (array $value) use ($attributeAsMainMedia) {
                    return $value['attribute'] === $attributeAsMainMedia;
                }
            )
        );

        if (false === $value) {
            return $emptyImage;
        }

        return $value['data'];
    }

    private function initUploadedFileData(array $override = []): array
    {
        $this->fileExists->save(self::UPDATED_DUMMY_FILE_FILEPATH);
        $fileData = array_merge([
            'originalFilename' => self::DUMMY_IMAGE_FILENAME,
            'filePath' => self::UPDATED_DUMMY_FILE_FILEPATH,
            'size' => self::DUMMY_IMAGE_SIZE,
            'mimeType' => self::DUMMY_IMAGE_MIMETYPE,
            'extension' => self::DUMMY_IMAGE_EXTENSION,
            'updatedAt' => '2019-11-22T15:16:21+0000',
        ], $override);
        $this->findFileData->save($fileData);

        return $fileData;
    }

    /**
     * @Given /^an asset family with a number attribute$/
     * @Given /^an asset family with a number attribute with decimals$/
     */
    public function aAssetFamilyWithANumberAttribute()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            NumberAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::NUMBER_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::NUMBER_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(true),
                AttributeLimit::limitless(),
                AttributeLimit::limitless()
            )
        );
    }

    /**
     * @Given /^an asset belonging to this asset family with values of "([^"]*)" for the number attribute$/
     * @Given /^an asset belonging to this asset family$/
     */
    public function aAssetBelongingToThisAssetFamilyWithValuesOfForTheNumberAttribute($numberValue = '0')
    {
        $assetValue = Value::create(
            AttributeIdentifier::create(
                self::ASSET_FAMILY_IDENTIFIER,
                self::NUMBER_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            NumberData::createFromNormalize($numberValue)
        );
        $this->createAsset($assetValue);
    }

    /**
     * @When /^the user updates the number attribute of the asset to "([^"]*)"$/
     */
    public function theUserUpdatesTheNumberAttributeOfTheAssetTo($newData)
    {
        $editCommand = $this->editAssetCommandFactory->create(
            [
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                        => self::ASSET_CODE,
                'labels'                      => [],
                'values'                      => [
                    [
                        'attribute' => self::NUMBER_ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => $newData,
                    ],
                ],
            ]
        );
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the asset should have the number value "([^"]*)" for this attribute$/
     */
    public function theAssetShouldHaveTheNumberValueForThisAttribute($expectedValue)
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::ASSET_CODE)
        );
        $value = $asset->findValue(
            ValueKey::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::NUMBER_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::notNull($value);
        Assert::same($expectedValue, $value->getData()->normalize());
    }

    /**
     * @Given /^an asset family with a number attribute with no decimal value$/
     */
    public function aAssetFamilyWithANumberAttributeWithNoDecimalValue()
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            NumberAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::NUMBER_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::NUMBER_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::limitless(),
                AttributeLimit::limitless()
            )
        );
    }

    /**
     * @Then /^there should be a validation error on the number value with message "([^\']*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyDecimalsAllowedAttributeWithMessage($expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage(
            'values.' . self::NUMBER_ATTRIBUTE_CODE,
            $expectedMessage
        );
    }

    /**
     * @Given /^an asset family with a number attribute with min "([^\']*)" and max "([^\']*)"$/
     */
    public function aAssetFamilyWithANumberAttributeWithMinAndMax(string $minValue, string $maxValue)
    {
        $this->createAssetFamily(self::ASSET_FAMILY_IDENTIFIER);
        $this->attributeRepository->create(
            NumberAttribute::create(
                AttributeIdentifier::create(
                    self::ASSET_FAMILY_IDENTIFIER,
                    self::NUMBER_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
                AttributeCode::fromString(self::NUMBER_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(2),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeDecimalsAllowed::fromBoolean(false),
                AttributeLimit::fromString($minValue),
                AttributeLimit::fromString($maxValue)
            )
        );
    }

    /**
     * @When /^the user updates the number value with an integer too long$/
     */
    public function theUserUpdatesTheNumberValueWithAnIntegerTooLong()
    {
        $editCommand = $this->editAssetCommandFactory->create(
            [
                'asset_family_identifier' => self::ASSET_FAMILY_IDENTIFIER,
                'code'                        => self::ASSET_CODE,
                'labels'                      => [],
                'values'                      => [
                    [
                        'attribute' => self::NUMBER_ATTRIBUTE_IDENTIFIER,
                        'channel'   => null,
                        'locale'    => null,
                        'data'      => self::INTEGER_TOO_LONG,
                    ],
                ],
            ]
        );
        $this->executeCommand($editCommand);
    }
}
