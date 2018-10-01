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

namespace Akeneo\ReferenceEntity\tests\back\Acceptance\Context;

use Akeneo\ReferenceEntity\Acceptance\Context\ConstraintViolationsContext;
use Akeneo\ReferenceEntity\Acceptance\Context\ExceptionContext;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCommandFactory;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\EditRecordHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditRecordContext implements Context
{
    private const REFERENCE_ENTITY_IDENTIFIER = 'designer';
    private const FINGERPRINT = 'fingerprint';
    private const RECORD_CODE = 'stark';

    private const ECOMMERCE_CHANNEL_CODE = 'ecommerce';
    private const FRENCH_LOCALE_CODE = 'fr_FR';

    private const TEXT_ATTRIBUTE_CODE = 'name';
    private const TEXT_ATTRIBUTE_IDENTIFIER = 'name_designer_fingerprint';
    private const IMAGE_ATTRIBUTE_CODE = 'primary_picture';
    private const IMAGE_ATTRIBUTE_IDENTIFIER = 'primary_picture_designer_fingerprint';
    private const DUMMY_ORIGINAL_VALUE = 'Une valeur naïve';
    private const DUMMY_UPDATED_VALUE = 'An updated dummy data';

    private const DUMMY_FILEPATH_PREFIX = '/a/dummy/key';
    private const UPDATED_FILE_PATH_PREFIX = '/tmp/an/updated/file/';
    private const INVALID_FILEPATH_VALUE = false;
    private const UPDATED_DUMMY_FILENAME = 'dummy_filename.png';
    private const INVALID_FILENAME = 144;
    private const FILE_TOO_BIG = 'too_big.jpeg';
    private const FILE_TOO_BIG_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::FILE_TOO_BIG;
    private const UPDATED_DUMMY_FILE_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::UPDATED_DUMMY_FILENAME;
    private const WRONG_EXTENSION_FILENAME = 'wrong_extension.gif';
    private const WRONG_EXTENSION_FILE_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::WRONG_EXTENSION_FILENAME;
    private const GOOD_EXTENSION_FILENAME = 'dummy_filename.png';
    private const GOOD_EXTENSION_FILE_FILEPATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'Common' . DIRECTORY_SEPARATOR . 'TestFixtures' . DIRECTORY_SEPARATOR . self::GOOD_EXTENSION_FILENAME;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var EditRecordCommandFactory */
    private $editRecordCommandFactory;

    /** @var EditRecordHandler */
    private $editRecordHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ExceptionContext */
    private $exceptionContext;

    /** @var ConstraintViolationsContext */
    private $violationsContext;


    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        EditRecordCommandFactory $editRecordCommandFactory,
        EditRecordHandler $editRecordHandler,
        ValidatorInterface $validator,
        ExceptionContext $exceptionContext,
        ConstraintViolationsContext $violationsContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->editRecordCommandFactory = $editRecordCommandFactory;
        $this->editRecordHandler = $editRecordHandler;
        $this->exceptionContext = $exceptionContext;
        $this->validator = $validator;
        $this->violationsContext = $violationsContext;
    }

    /**
     * @Given /^an reference entity with a text attribute$/
     * @throws \Exception
     */
    public function anReferenceEntityWithATextAttribute()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^a record belonging to this reference entity with a value of "([^"]*)" for the text attribute$/
     */
    public function aRecordBelongingToThisReferenceEntityWithAValueOfFor(string $textData)
    {
        $textValue = Value::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString($textData)
        );
        $this->createRecord($textValue);
    }

    /**
     * @When /^the user updates the text attribute of the record to "([^"]*)"$/
     */
    public function theUserUpdatesTheTextOfOfTheRecordTo(string $newData): void
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Then /^the record should have the text value "([^"]*)" for this attribute$/
     */
    public function theRecordShouldHaveTheTextValueFor(string $expectedValue): void
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($value);
        Assert::assertEquals($expectedValue, $value->getData()->normalize());
    }

    /**
     * @Given /^an reference entity with an image attribute$/
     */
    public function anReferenceEntityWithAImageAttribute()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::noLimit(),
                AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED)
            )
        );
    }

    /**
     * @Given /^a record belonging to this reference entity with the file "([^"]*)" for the image attribute$/
     */
    public function aRecordBelongingToThisReferenceEntityWithATheFileForTheImageAttribute(string $originalFilename)
    {
        $file = new FileInfo();
        $file->setOriginalFilename($originalFilename);
        $file->setKey(self::DUMMY_FILEPATH_PREFIX . $originalFilename);

        $fileValue = Value::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::IMAGE_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            FileData::createFromFileinfo($file)
        );
        $this->createRecord($fileValue);
    }

    /**
     * @When /^the user updates the image attribute of the record with a valid file$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordTo()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::UPDATED_DUMMY_FILENAME,
                        'filePath'         => self::UPDATED_DUMMY_FILE_FILEPATH
                    ],
                ],
            ],
        ]);

        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^the record should have the valid image for this attribute$/
     */
    public function theRecordShouldHaveTheImageForThisAttribute()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::IMAGE_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );
        Assert::assertNotNull($value);
        $normalizeData = $value->getData()->normalize();
        Assert::assertArrayHasKey('originalFilename', $normalizeData);
        Assert::assertArrayHasKey('filePath', $normalizeData);
        Assert::assertEquals(self::UPDATED_DUMMY_FILENAME, $normalizeData['originalFilename']);
        Assert::assertEquals(self::UPDATED_DUMMY_FILE_FILEPATH, $normalizeData['filePath']);
    }

    /**
     * @Then /^there should be a validation error on the property text attribute with message "([^\']*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyTextAttributeWithMessage(string $expectedMessage)
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage('values.' . self::TEXT_ATTRIBUTE_CODE, $expectedMessage);
    }

    /**
     * @Given /^an reference entity with a text attribute with max length (\d+)$/
     * @throws \Exception
     */
    public function anReferenceEntityWithATextAttributeWithMaxLength(int $maxLength)
    {
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^an reference entity with a text attribute with an email validation rule$/
     * @throws \Exception
     */
    public function anReferenceEntityWithATextAttributeWithAnEmailValidationRule()
    {
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^an reference entity with a text attribute with a regular expression validation rule like "([^"]*)"$/
     * @throws \Exception
     */
    public function anReferenceEntityWithATextAttributeWithARegularExpressionValidationRuleLike(string $regularExpression
    ): void {
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @When /^the user updates the text attribute of the record to an invalid value type$/
     */
    public function theUserUpdatesTheTextAttributeOfTheRecordToAnInvalidValue()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
    }

    /**
     * @Given /^an reference entity with a text attribute with an url validation rule$/
     * @throws \Exception
     */
    public function anReferenceEntityWithATextAttributeWithAnUrlValidationRule()
    {
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @When /^the user empties the text attribute of the record$/
     */
    public function theUserEmptiesTheTextAttributeOfTheRecord()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Given /^the record should have an empty value for this attribute$/
     */
    public function theRecordShouldHaveAnEmptyValueForThisAttribute()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNull($value);
    }

    /**
     * @Given /^an reference entity with a localizable attribute$/
     * @throws \Exception
     */
    public function anReferenceEntityWithALocalizableAttribute()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^a record belonging to this reference entity with a value for the french locale$/
     */
    public function aRecordBelongingToThisReferenceEntityWithAValueForTheFrenchLocale()
    {
        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE)),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createRecord($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the record for the french locale$/
     */
    public function theUserUpdatesTheAttributeOfTheRecordForTheFrenchLocale()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Given /^the record should have the updated value for this attribute and the french locale$/
     */
    public function theRecordShouldHaveTheUpdatedValueForThisAttributeAndTheFrenchLocale()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE))
            )
        );

        Assert::assertNotNull($value);
        Assert::assertEquals(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @Given /^an reference entity with a scopable attribute$/
     * @throws \Exception
     */
    public function anReferenceEntityWithAScopableAttribute()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^a record belonging to this reference entity with a value for the ecommerce channel$/
     */
    public function aRecordBelongingToThisReferenceEntityWithAValueForTheEcommerceChannel()
    {
        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
            LocaleReference::noReference(),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createRecord($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the record for the ecommerce channel$/
     */
    public function theUserUpdatesTheAttributeOfTheRecordForTheEcommerceChannel()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Given /^the record should have the updated value for this attribute and the ecommerce channel$/
     */
    public function theRecordShouldHaveTheUpdatedValueForThisAttributeAndTheEcommerceChannel()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
                LocaleReference::noReference()
            )
        );
        Assert::assertNotNull($value);
        Assert::assertEquals(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @Given /^an reference entity with a scopable and localizable attribute$/
     */
    public function anReferenceEntityWithAScopableAndLocalizableAttribute()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::TEXT_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::TEXT_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
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
     * @Given /^a record belonging to this reference entity with a value for the ecommerce channel and french locale$/
     */
    public function aRecordBelongingToThisReferenceEntityWithAValueForTheEcommerceChannelAndFrenchLocale()
    {
        $localizedValue = Value::create(
            AttributeIdentifier::create(
                self::REFERENCE_ENTITY_IDENTIFIER,
                self::TEXT_ATTRIBUTE_CODE,
                self::FINGERPRINT
            ),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE)),
            TextData::fromString(self::DUMMY_ORIGINAL_VALUE)
        );
        $this->createRecord($localizedValue);
    }

    /**
     * @When /^the user updates the attribute of the record for the ecommerce channel and french locale$/
     */
    public function theUserUpdatesTheAttributeOfTheRecordForTheEcommerceChannelAndFrenchLocale()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Given /^the record should have the updated value for this attribute and the ecommerce channel and the french locale$/
     */
    public function theRecordShouldHaveTheUpdatedValueForThisAttributeAndTheEcommerceChannelAndTheFrenchLocale()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::TEXT_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode(self::ECOMMERCE_CHANNEL_CODE)),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode(self::FRENCH_LOCALE_CODE))
            )
        );
        Assert::assertNotNull($value);
        Assert::assertEquals(self::DUMMY_UPDATED_VALUE, $value->getData()->normalize());
    }

    /**
     * @When /^the user updates the attribute of the record with an invalid channel$/
     */
    public function theUserUpdatesTheAttributeOfTheRecordForAnInvalidChannel()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @When /^the user updates the attribute of the record for an unknown channel$/
     */
    public function theUserUpdatesTheAttributeOfTheRecordForAnUnknownChannel()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::TEXT_ATTRIBUTE_IDENTIFIER,
                    'channel'   => 'Unknown channel',
                    'locale'    => null,
                    'data'      => self::DUMMY_UPDATED_VALUE,
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the record to an invalid file path$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordToAnInvalidFilepath()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Then /^there should be a validation error on the property image attribute with message "([^"]*)"$/
     */
    public function thereShouldBeAValidationErrorOnThePropertyImageAttributeWithMessage(string $expectedMessage): void
    {
        $this->violationsContext->assertThereShouldBeViolations(1);
        $this->violationsContext->assertViolationOnPropertyWithMesssage('values.' . self::IMAGE_ATTRIBUTE_CODE, $expectedMessage);
    }

    /**
     * @When /^the user updates the image attribute of the record to an invalid file name$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordToAnInvalidFileName()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @When /^the user updates the image attribute of the record with a bigger file than the limit$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordWithABiggerFileThanTheLimit()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::FILE_TOO_BIG,
                        'filePath'         => self::FILE_TOO_BIG_FILEPATH
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the record with a smaller file than the limit$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordWithASmallerFileThanTheLimit()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::GOOD_EXTENSION_FILENAME,
                        'filePath'         => self::GOOD_EXTENSION_FILE_FILEPATH
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an reference entity with an image attribute having a max file size of 15ko$/
     */
    public function anReferenceEntityWithAnImageAttributeHavingAMaxFileSizeOf10k()
    {
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('0.015'),
                AttributeAllowedExtensions::fromList([])
            )
        );
    }

    /**
     * @When /^the user updates the image attribute of the record with a gif file which is a denied extension$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordWithAFileHavingADeniedExtension()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::WRONG_EXTENSION_FILENAME,
                        'filePath'         => self::WRONG_EXTENSION_FILE_FILEPATH
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @When /^the user updates the image attribute of the record with a png file$/
     */
    public function theUserUpdatesTheImageAttributeOfTheRecordWithAFileHavingAValidExtension()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [],
            'values'                     => [
                [
                    'attribute' => self::IMAGE_ATTRIBUTE_IDENTIFIER,
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => [
                        'originalFilename' => self::GOOD_EXTENSION_FILENAME,
                        'filePath'         => self::GOOD_EXTENSION_FILE_FILEPATH
                    ],
                ],
            ],
        ]);
        $this->executeCommand($editCommand);
    }

    /**
     * @Given /^an reference entity with an image attribute allowing only files with extension png$/
     */
    public function anReferenceEntityWithAnImageAttributeAllowingOnlyFilesWithExtensionJpeg()
    {
        $this->createReferenceEntity();
        $this->attributeRepository->create(
            ImageAttribute::create(
                AttributeIdentifier::create(
                    self::REFERENCE_ENTITY_IDENTIFIER,
                    self::IMAGE_ATTRIBUTE_CODE,
                    self::FINGERPRINT
                ),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                AttributeCode::fromString(self::IMAGE_ATTRIBUTE_CODE),
                LabelCollection::fromArray([]),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(false),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxFileSize::fromString('150.110'),
                AttributeAllowedExtensions::fromList(['png'])
            )
        );
    }

    /**
     * @When /^the user removes an image from the record for this attribute$/
     */
    public function theUserRemovesAnImageFromTheRecordForThisAttribute()
    {
        $editCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
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
     * @Given /^the record should not have any image for this attribute$/
     */
    public function theRecordShouldNotHaveAnyImageForThisAttribute()
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        $value = $record->findValue(
            ValueKey::create(
                AttributeIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::IMAGE_ATTRIBUTE_CODE, self::FINGERPRINT),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );
        Assert::assertNull($value);
    }

    /**
     * @Given /^an referenceEntity and a record with french label "([^"]*)"$/
     */
    public function anReferenceEntityAndARecordWithLabel(string $label): void
    {
        $this->createReferenceEntity();
        $this->recordRepository->create(
            Record::create(
                RecordIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE),
                ['fr_FR' => $label],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
    }

    /**
     * @When /^the user updates the french label to "([^"]*)"$/
     */
    public function theUserUpdatesTheLabelTo(string $updatedLabel)
    {
        $editLabelCommand = $this->editRecordCommandFactory->create([
            'reference_entity_identifier' => self::REFERENCE_ENTITY_IDENTIFIER,
            'code'                       => self::RECORD_CODE,
            'labels'                     => [
                'fr_FR' => $updatedLabel
            ],
            'values'                     => [],
        ]);
        $this->executeCommand($editLabelCommand);
    }

    /**
     * @Then /^the record should have the french label "([^"]*)"$/
     */
    public function theRecordShouldHaveTheLabel(string $expectedLabel)
    {
        $record = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            RecordCode::fromString(self::RECORD_CODE)
        );
        Assert::assertEquals($expectedLabel, $record->getLabel('fr_FR'), 'Labels are not equal');
    }

    private function createReferenceEntity(): void
    {
        $this->referenceEntityRepository->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
            [],
            Image::createEmpty()
        ));
    }

    private function createRecord(Value $value): void
    {
        $this->recordRepository->create(
            Record::create(
                RecordIdentifier::create(self::REFERENCE_ENTITY_IDENTIFIER, self::RECORD_CODE, self::FINGERPRINT),
                ReferenceEntityIdentifier::fromString(self::REFERENCE_ENTITY_IDENTIFIER),
                RecordCode::fromString(self::RECORD_CODE),
                [],
                Image::createEmpty(),
                ValueCollection::fromValues([$value])
            )
        );
    }

    private function executeCommand(EditRecordCommand $editCommand): void
    {
        $violations = $this->validator->validate($editCommand);
        if ($violations->count() > 0) {
            $this->violationsContext->addViolations($violations);

            return;
        }

        try {
            ($this->editRecordHandler)($editCommand);
        } catch (\Exception $e) {
            $this->exceptionContext->setException($e);
        }
    }
}
