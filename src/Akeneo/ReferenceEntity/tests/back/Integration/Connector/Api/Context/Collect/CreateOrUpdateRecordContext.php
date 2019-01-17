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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Collect;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFileExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryGetAttributeIdentifier;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
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
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateRecordContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Record/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var null|Response */
    private $uploadImageResponse;

    /** @var InMemoryFindFileDataByFileKey */
    private $findFileData;

    /** @var InMemoryFileExists */
    private $fileExists;

    /** @var InMemoryGetAttributeIdentifier */
    private $getAttributeIdentifier;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        RecordRepositoryInterface $recordRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        InMemoryFindFileDataByFileKey $findFileData,
        InMemoryFileExists $fileExists,
        InMemoryGetAttributeIdentifier $getAttributeIdentifier
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordRepository = $recordRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->findFileData = $findFileData;
        $this->fileExists = $fileExists;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
    }

    /**
     * @Given a record of the Brand reference entity existing in the ERP but not in the PIM
     */
    public function aRecordOfTheBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_kartell_record_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadDescriptionAttribute();
        $this->loadBrandReferenceEntity();
    }

    /**
     * @When the connector collects this record from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThisRecordFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the record is created in the PIM with the information from the ERP
     */
    public function theRecordIsCreatedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_record_creation.json'
        );

        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell')
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedRecord = Record::create(
            $kartellRecord->getIdentifier(),
            $referenceEntityIdentifier,
            RecordCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french description.')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description.')
                ),
            ])
        );

        Assert::assertEquals($expectedRecord, $kartellRecord);
    }

    /**
     * @Given a record of the Brand reference entity existing in the ERP and the PIM with different information
     */
    public function aRecordOfTheBrandReferenceEntityExistingInTheErpAndThePimWithDifferentInformation()
    {
        $this->requestContract = 'successful_kartell_record_update.json';

        $this->loadBrandReferenceEntity();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadCoverImageAttribute();
        $this->loadBrandKartellRecord();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->findFileData->save([
            'filePath'         => '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg',
            'originalFilename' => 'kartell_cover.jpg',
            'size'             => 128,
            'mimeType'         => 'image/jpeg',
            'extension'        => 'jpg'
        ]);
        $this->fileExists->save('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg');
    }

    /**
     * @Then the record is correctly synchronized in the PIM with the information from the ERP
     */
    public function theRecordIsCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_record_update.json'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            $referenceEntityIdentifier,
            RecordCode::fromString('kartell')
        );

        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('image')
        );

        $coverImageInfo = (new FileInfo())
            ->setKey('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg')
            ->setOriginalFilename('kartell_cover.jpg')
            ->setSize(128)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $expectedKartellRecord = Record::create(
            RecordIdentifier::fromString('brand_kartell_fingerprint'),
            $referenceEntityIdentifier,
            RecordCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description')
                ),
                Value::create(
                    AttributeIdentifier::fromString('cover_image_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($coverImageInfo)
                ),
            ])
        );

        Assert::assertEquals($expectedKartellRecord, $kartellRecord);
    }

    /**
     * @When the connector collects a record that has an invalid format
     */
    public function theConnectorCollectsARecordThatHasAnInvalidFormat()
    {
        $this->loadNameAttribute();
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the record has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheRecordHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects a record whose data does not comply with the business rules
     */
    public function theConnectorCollectsARecordWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['fr_FR', 'en_US']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $this->loadDescriptionAttribute();
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the record has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheRecordHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_record_for_invalid_data.json'
        );
    }

    /**
     * @Given some records of the Brand reference entity existing in the ERP but not in the PIM
     */
    public function someRecordsOfTheBrandReferenceEntityExistingInTheErpButNotInThePim()
    {
        $this->loadBrandReferenceEntity();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Given some records of the Brand reference entity existing in the ERP and in the PIM but with different information
     */
    public function someRecordsOfTheBrandReferenceEntityExistingInTheErpAndInThePimButWithDifferentInformation()
    {
        $this->loadBrandKartellRecord();
        $this->loadBrandLexonRecord();
    }

    /**
     * @When the connector collects these records from the ERP to synchronize them with the PIM
     */
    public function theConnectorCollectsTheseRecordsFromTheErpToSynchronizeThemWithThePim()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_synchronization.json'
        );
    }

    /**
     * @Then the records that existed only in the ERP are correctly created in the PIM
     */
    public function theRecordsThatExistedOnlyInTheErpAreCorrectlyCreatedInThePim()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_synchronization.json'
        );

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $fatboyRecord = $this->recordRepository->getByReferenceEntityAndCode(
            $referenceEntityIdentifier,
            RecordCode::fromString('fatboy')
        );

        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedFatboyRecord = Record::create(
            $fatboyRecord->getIdentifier(),
            $referenceEntityIdentifier,
            RecordCode::fromString('fatboy'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Fatboy label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Fatboy name')
                )
            ])
        );

        Assert::assertEquals($expectedFatboyRecord, $fatboyRecord);
    }

    /**
     * @Then the records existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
     */
    public function theRecordsExistingBothInTheErpAndThePimAreCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('image')
        );

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $expectedKartellRecord = Record::create(
            RecordIdentifier::fromString('brand_kartell_fingerprint'),
            $referenceEntityIdentifier,
            RecordCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description')
                )
            ])
        );

        $kartellRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('kartell')
        );

        Assert::assertEquals($expectedKartellRecord, $kartellRecord);

        $expectedLexonRecord = Record::create(
            RecordIdentifier::fromString('brand_lexon_fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('lexon'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon updated english label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated Lexon english name')
                )
            ])
        );

        $lexonRecord = $this->recordRepository->getByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString('brand'),
            RecordCode::fromString('lexon')
        );

        Assert::assertEquals($expectedLexonRecord, $lexonRecord);
    }

    /**
     * @When the connector collects records from the ERP among which some records have data that do not comply with the business rules
     */
    public function theConnectorCollectsRecordsFromTheErpAmongWhichSomeRecordsHaveDataThatDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_records_with_unprocessable_records.json'
        );
    }

    /**
     * @Then the PIM notifies the connector which records have data that do not comply with the business rules and what are the errors
     */
    public function thePimNotifiesTheConnectorWhichRecordsHaveDataThatDoNotComplyWithTheBusinessRulesAndWhatAreTheErrors()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_records_with_unprocessable_records.json'
        );
    }

    /**
     * @When the connector collects a number of records exceeding the maximum number of records in one request
     */
    public function theConnectorCollectsANumberOfRecordsExceedingTheMaximumNumberOfRecordsInOneRequest()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'too_many_records_to_process.json'
        );
    }

    /**
     * @Then the PIM notifies the connector that there were too many records to collect in one request
     */
    public function thePimNotifiesTheConnectorThatThereWereTooManyRecordsToCollectInOneRequest()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'too_many_records_to_process.json'
        );
    }

    /**
     * @Given /^the Kartell record of the Brand reference entity without any media file$/
     */
    public function theKartellRecordOfTheBrandReferenceEntityWithoutAnyMediaFile()
    {
        $this->loadBrandReferenceEntity();
        $this->loadCoverImageAttribute();
        $this->loadBrandKartellRecord();
    }

    /**
     * @When /^the connector collects a media file for the Kartell record from the DAM to synchronize it with the PIM$/
     */
    public function theConnectorCollectsAMediaFileForTheKartellRecordFromTheDAMToSynchronizeItWithThePIM()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->uploadImageResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_image_upload.json"
        );
    }

    /**
     * @Then /^the Kartell record is correctly synchronized with the uploaded media file$/
     */
    public function theKartellRecordIsCorrectlySynchronizedWithTheUploadedMediaFile()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->uploadImageResponse,
            self::REQUEST_CONTRACT_DIR ."successful_image_upload.json"
        );
    }

    private function loadBrandReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadDescriptionAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadNameAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadCoverImageAttribute(): void
    {
        $image = ImageAttribute::create(
            AttributeIdentifier::create('brand', 'cover_image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('cover_image'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($image);
    }

    private function loadBrandKartellRecord(): void
    {
        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('image')
        );

        $record = Record::create(
            RecordIdentifier::fromString('brand_kartell_fingerprint'),
            $referenceEntityIdentifier,
            RecordCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell English label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french name')
                )
            ])
        );

        $this->recordRepository->create($record);
    }

    private function loadBrandLexonRecord(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withReferenceEntityAndCode(
            $referenceEntityIdentifier,
            AttributeCode::fromString('label')
        );

        $record = Record::create(
            RecordIdentifier::fromString('brand_lexon_fingerprint'),
            $referenceEntityIdentifier,
            RecordCode::fromString('lexon'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon description')
                )
            ])
        );

        $this->recordRepository->create($record);
    }
}
