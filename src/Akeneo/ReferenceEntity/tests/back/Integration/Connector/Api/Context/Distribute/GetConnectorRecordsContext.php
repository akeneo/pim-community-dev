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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Distribute;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorRecordsByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryDateRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordIdentifiersForQuery;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
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
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetConnectorRecordsContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Record/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorRecordsByIdentifiers */
    private $findConnectorRecords;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var array */
    private $recordPages;

    /** @var InMemoryFindRecordIdentifiersForQuery */
    private $findRecordIdentifiersForQuery;

    /** @var null|Response */
    private $unprocessableEntityResponse;

    /** @var null|Response */
    private $updatedSinceWrongFormatResponse;

    /** @var null|Response */
    private $updatedSinceResponse;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var ConnectorRecord[] */
    private $connectorRecordsByRecordIdentifier;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $findActivatedLocalesByIdentifiers;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $findRequiredValueKeyCollectionForChannelAndLocales;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $findActivatedLocalesPerChannels;

    /** @var InMemoryDateRepository */
    private $dateRepository;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindRecordIdentifiersForQuery $findRecordIdentifiersForQuery,
        InMemoryFindConnectorRecordsByIdentifiers $findConnectorRecords,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        InMemoryAttributeRepository $attributeRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales,
        InMemoryFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        InMemoryDateRepository $dateRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorRecords = $findConnectorRecords;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordPages = [];
        $this->findRecordIdentifiersForQuery = $findRecordIdentifiersForQuery;
        $this->channelExists = $channelExists;
        $this->findActivatedLocalesByIdentifiers = $findActivatedLocalesByIdentifiers;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->dateRepository = $dateRepository;
    }

    /**
     * @Given /^([\d]+) records for the ([\S]+) reference entity$/
     */
    public function theRecordsForTheReferenceEntity(int $numberOfRecords, string $referenceEntityIdentifier): void
    {
        $referenceEntityIdentifier = strtolower($referenceEntityIdentifier);

        for ($i = 1; $i <= $numberOfRecords; $i++) {
            $rawRecordCode = sprintf('%s_%d', $referenceEntityIdentifier, $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));
            $labelCollection = [
                'en_US' => sprintf('%s number %d', ucfirst($referenceEntityIdentifier), $i)
            ];

            $mainImageInfo = (new FileInfo())
                ->setOriginalFilename(sprintf('%s_image.jpg', $rawRecordCode))
                ->setKey(sprintf('test/%s_image.jpg', $rawRecordCode));
            $mainImage = Image::fromFileInfo($mainImageInfo);

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('%s number %d', ucfirst($referenceEntityIdentifier), $i))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('main_image_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromFileinfo($mainImageInfo)
                    ),
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => null,
                            'data' => sprintf('%s example %d', ucfirst($referenceEntityIdentifier), $i)
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => 'italy'
                        ]
                    ],
                    'image' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => $mainImage->getKey()
                        ]
                    ]
                ]
            );

            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When /^the connector requests all records of the ([\S]+) reference entity$/
     */
    public function theConnectorRequestsAllRecordsOfTheReferenceEntity(string $referenceEntityIdentifier): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages = [];

        for ($page = 1; $page <= 4; $page++) {
            $this->recordPages[$page] = $this->webClientHelper->requestFromFile(
                $client,
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_%s_records_page_%d.json",
                    strtolower($referenceEntityIdentifier),
                    $page
                )
            );
        }
    }

    /**
     * @Then /^the PIM returns the [\d]+ records of the ([\S]+) reference entity$/
     */
    public function thePimReturnsTheRecordsOfTheReferenceEntity(string $referenceEntityIdentifier): void
    {
        for ($page = 1; $page <= 4; $page++) {
            Assert::keyExists($this->recordPages, $page, sprintf('The page %d has not been loaded', $page));
            $this->webClientHelper->assertJsonFromFile(
                $this->recordPages[$page],
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_%s_records_page_%d.json",
                    strtolower($referenceEntityIdentifier),
                    $page
                )
            );
        }
    }

    /**
     * @Given 3 records for the Brand reference entity with filled attribute values for the Ecommerce and the Tablet channels
     */
    public function theRecordsForTheBrandReferenceEntityWithFilledAttributesValuesForTwoChannels(): void
    {
        $referenceEntityIdentifier = 'brand';
        $firstChannel = 'ecommerce';
        $secondChannel = 'tablet';

        $this->channelExists->save(ChannelIdentifier::fromCode($firstChannel));
        $this->channelExists->save(ChannelIdentifier::fromCode($secondChannel));

        for ($i = 1; $i <= 3; $i++) {
            $rawRecordCode = sprintf('%s_%d', $referenceEntityIdentifier, $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));
            $labelCollection = [
                'en_US' => sprintf('%s number %d', ucfirst($referenceEntityIdentifier), $i)
            ];

            $mainImageInfo = (new FileInfo())
                ->setOriginalFilename(sprintf('%s_image.jpg', $rawRecordCode))
                ->setKey(sprintf('test/%s_image.jpg', $rawRecordCode));
            $mainImage = Image::fromFileInfo($mainImageInfo);

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('%s number %d', ucfirst($referenceEntityIdentifier), $i))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('main_image_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        FileData::createFromFileinfo($mainImageInfo)
                    ),
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => $firstChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($referenceEntityIdentifier),
                                $i,
                                $firstChannel
                            )
                        ],
                        [
                            'locale' => 'en_US',
                            'channel' => $secondChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($referenceEntityIdentifier),
                                $i,
                                $secondChannel
                            )
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => 'italy'
                        ]
                    ],
                    'image' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => $mainImage->getKey()
                        ]
                    ]
                ]
            );

            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When the connector requests all records of the Brand reference entity with the information of the Ecommerce channel
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheInformationOfTheEcommerceChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages = [];

        $this->recordPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_ecommerce_channel.json'
        );
    }

    /**
     * @Then the PIM returns 3 records of the Brand reference entity with only the information of the Ecommerce channel
     */
    public function thePimReturnsAllRecordsOfTheBrandReferenceEntityWithOnlyAttributeValuesOfTheEcommerceChannel(): void
    {
        Assert::keyExists($this->recordPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->recordPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_ecommerce_channel.json'
        );
    }

    /**
     * @When the connector requests all records of the Brand reference entity with the information of a non-existent channel
     */
    public function theConnectorRequestAllRecordsOfTheBrandReferenceEntityWithTheInformationOfANonExistentChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_records_for_non_existent_channel.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the provided channel does not exist
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheProvidedChannelDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->unprocessableEntityResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Given 3 records for the Brand reference entity with filled attribute values for the English and the French locales
     */
    public function theRecordsForTheBrandReferenceEntityWithFilledAttributeValuesForEnglishAndFrenchLocales()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));

        for ($i = 1; $i <= 3; $i++) {
            $rawRecordCode = sprintf('brand_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand'),
                $recordCode,
                ValueCollection::fromValues([])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => sprintf('English label for %s', $rawRecordCode)
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => null,
                            'value'   => sprintf('French label for %s', $rawRecordCode)
                        ]
                    ],
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => 'ecommerce',
                            'data' => sprintf('Description for the brand number %d', $i)
                        ],
                        [
                            'locale' => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data' => sprintf('Description pour la marque numero %d', $i)
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => 'en_US',
                            'channel' => null,
                            'data' => 'Italy'
                        ],
                        [
                            'locale' => 'fr_FR',
                            'channel' => null,
                            'data' => 'Italie'
                        ]
                    ]
                ]
            );

            $this->connectorRecordsByRecordIdentifier[(string) $recordIdentifier] = $connectorRecord;
            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When the connector requests all records of the Brand reference entity with the information in English
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheInformationInEnglish()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_english_locale.json'
        );
    }

    /**
     * @Then the PIM returns 3 records of the Brand reference entity with the information in English only
     */
    public function thePimReturnsTheRecordsOfTheBrandReferenceEntityWithTheInformationInEnglishOnly()
    {
        Assert::keyExists($this->recordPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->recordPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_english_locale.json'
        );
    }

    /**
     * @When the connector requests all records of the Brand reference entity with the information of a provided locale that does not exist
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheAttributesValuesOfAProvidedLocaleThatDoesNotExist()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_records_for_non_existent_locale.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the provided locale does not exist
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheProvidedLocaleDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->unprocessableEntityResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_brand_records_for_non_existent_locale.json'
        );
    }

    /**
     * @Given /^([\d]+) records for the Brand reference entity on the Ecommerce channel that are incomplete for the French locale but complete for the English locale$/
     */
    public function recordsForTheBrandReferenceEntityOnTheEcommerceChannelThatAreIncompleteForTheFrenchLocaleButCompleteForTheEnglishLocale(int $numberOfRecords)
    {
        $this->findActivatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->loadBrandReferenceEntity();
        $this->loadRequiredAttribute();
        $this->loadNotRequiredAttribute();

        for ($i = 1; $i <= $numberOfRecords; $i++) {
            $rawRecordCode = sprintf('incomplete_french_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));
            $labelCollection = [
                'en_US' => sprintf('Incomplete french Brand record number %d', $i)
            ];

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand'),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Incomplete french Brand record number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce en_US'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }
    }

    /**
     * @Given /^([\d]+) records for the Brand reference entity on the Ecommerce channel that are complete for the French locale but that are incomplete for the English locale$/
     */
    public function recordsForTheBrandReferenceEntityOnTheEcommerceChannelThatAreCompleteForTheFrenchLocaleButThatAreIncompleteForTheEnglishLocale(int $numberOfRecords)
    {
        for ($i = 1; $i <= $numberOfRecords; $i++) {
            $rawRecordCode = sprintf('incomplete_english_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));
            $labelCollection = [
                'en_US' => sprintf('Incomplete english Brand record number %d', $i)
            ];

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand'),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Incomplete english Brand record number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Required attribute ecommerce fr_FR')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce fr_FR'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }
    }

    /**
     * @Given /^([\d]+) records for the Brand reference entity on the Ecommerce channel that are both complete for the French and the English locale$/
     */
    public function recordsForTheBrandReferenceEntityOnTheEcommerceChannelThatAreBothCompleteForTheFrenchAndTheEnglishLocale(int $numberOfRecords)
    {
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedChannels(['ecommerce']);
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedLocales(['en_US', 'fr_FR']);

        for ($i = 1; $i <= $numberOfRecords; $i++) {
            $rawRecordCode = sprintf('complete_brand_record_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));
            $labelCollection = [
                'en_US' => sprintf('Complete Brand record number %d', $i)
            ];

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand'),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Complete Brand record number %d', $i))
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Required attribute ecommerce fr_FR')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::fromString('Not Required attribute ecommerce en_US')
                    ),
                    Value::create(
                        AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
                        ChannelReference::createFromNormalized('ecommerce'),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::fromString('Not Required attribute ecommerce fr_FR')
                    )
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                [
                    'label' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => null,
                            'value'   => $labelCollection['en_US']
                        ],
                    ],
                    'required_attribute' => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce fr_FR'
                        ],
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Required attribute ecommerce en_US'
                        ]
                    ],
                    'not_required_attribute' => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce en_US'
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => 'Not required attribute ecommerce fr_FR'
                        ]
                    ]
                ]
            );

            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }
    }

    /**
     * @When the connector requests all complete records of the Brand reference entity on the Ecommerce channel for the French and English locales
     */
    public function theConnectorRequestsAllCompleteRecordsOfTheBrandReferenceEntityOnTheEcommerceChannelForTheFrenchAndEnglishLocales()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_complete_brand_records.json'
        );
    }

    /**
     * @Then the PIM returns the 2 complete records of the Brand reference entity on the Ecommerce channel for the French and English locales
     */
    public function thePimReturnsThe2CompleteRecordsOfTheBrandReferenceEntityOnTheEcommerceChannelForTheFrenchAndEnglishLocales()
    {
        Assert::keyExists($this->recordPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->recordPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_complete_brand_records.json'
        );
    }

    /**
     * @When /^the connector requests all complete records of the Brand reference entity on a channel that does not exist$/
     */
    public function theConnectorRequestsAllCompleteRecordsOfTheBrandReferenceEntityOnAChannelThatDoesNotExist()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_records_filtered_on_completeness_for_a_non_existent_channel.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector requests all complete records of the Brand reference entity on the Ecommerce channel for a not activated locale$/
     */
    public function theConnectorRequestsAllCompleteRecordsOfTheBrandReferenceEntityOnTheEcommerceChannelForANotActivatedLocale()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->requestContract = 'unprocessable_entity_brand_records_filtered_on_completeness_for_a_non_existent_locale.json';

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    private function loadBrandReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadRequiredAttribute(): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'required_attribute', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('required_attribute'),
            LabelCollection::fromArray(['en_US' => 'Required attribute']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }

    private function loadNotRequiredAttribute(): void
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'not_required_attribute', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('not_required_attribute'),
            LabelCollection::fromArray(['en_US' => 'Not required attribute']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($attribute);
    }

    /**
     * @Given /^2 records for the Brand reference entity that were last updated on the 10th of October (\d+)$/
     */
    public function recordsForTheBrandReferenceEntityThatWereLastUpdatedOnThe10thOfOctober()
    {
        $this->dateRepository->setCurrentDate(new \DateTime('2018-10-10'));

        for ($i = 4; $i >= 2; $i--) {
            $rawRecordCode = sprintf('brand_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand_test'),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString('test')
                    ),
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                []
            );

            $this->connectorRecordsByRecordIdentifier[(string) $recordIdentifier] = $connectorRecord;
            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand_test'),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @Given /^2 records for the Brand reference entity that were updated on the 15th of October 2018$/
     */
    public function recordsForTheBrandReferenceEntityThatWereUpdatedOnThe15thOfOctober()
    {
        $this->dateRepository->setCurrentDate(new \DateTime('2018-10-15'));

        for ($i = 2; $i >= 0; $i--) {
            $rawRecordCode = sprintf('brand_%d', $i);
            $recordCode = RecordCode::fromString($rawRecordCode);
            $recordIdentifier = RecordIdentifier::fromString(sprintf('%s_fingerprint', $rawRecordCode));

            Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand_test'),
                $recordCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_brand_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString('test')
                    ),
                ])
            );

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                []
            );

            $this->connectorRecordsByRecordIdentifier[(string) $recordIdentifier] = $connectorRecord;
            $this->findConnectorRecords->save($recordIdentifier, $connectorRecord);
        }
    }

    /**
     * @When /^the connector requests all records of the Brand reference entity updated since the 14th of October 2018$/
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityUpdatedSinceThe14thOfOctober()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->updatedSinceResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'updated_since_brand_records_page_1.json'
        );
    }

    /**
     * @Then /^the PIM returns the 2 records of the Brand reference entity that were updated on the 15th of October 2018$/
     */
    public function thePIMReturnsTheRecordsOfTheBrandReferenceEntityThatWereUpdatedOnThe15thOfOctober()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->updatedSinceResponse,
            self::REQUEST_CONTRACT_DIR . 'updated_since_brand_records_page_1.json'
        );
    }

    /**
     * @When /^the connector requests records that were updated since a date that does not have the right format$/
     */
    public function theConnectorRequestsRecordsThatWereUpdatedSinceADateThatDoesNotHaveTheRightFormat()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->updatedSinceWrongFormatResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'updated_entity_brand_records_for_wrong_format.json'
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the date format is not the expected one$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheDateFormatIsNotTheExpectedOne()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->updatedSinceWrongFormatResponse,
            self::REQUEST_CONTRACT_DIR . 'updated_entity_brand_records_for_wrong_format.json'
        );
    }
}
