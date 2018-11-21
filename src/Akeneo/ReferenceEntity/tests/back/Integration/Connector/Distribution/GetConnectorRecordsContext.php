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

namespace Akeneo\ReferenceEntity\Integration\Connector\Distribution;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorRecordsByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryChannelExists;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordIdentifiersForQuery;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
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

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var array */
    private $recordPages;

    /** @var InMemoryFindRecordIdentifiersForQuery */
    private $findRecordIdentifiersForQuery;

    /** @var null|Response */
    private $unprocessableEntityResponse;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var ConnectorRecord[] */
    private $connectorRecordsByRecordIdentifier;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $findActivatedLocalesByIdentifiers;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindRecordIdentifiersForQuery $findRecordIdentifiersForQuery,
        InMemoryFindConnectorRecordsByIdentifiers $findConnectorRecords,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $findActivatedLocalesByIdentifiers
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

            $record = Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                $recordCode,
                $labelCollection,
                $mainImage,
                ValueCollection::fromValues([])
            );

            $this->findRecordIdentifiersForQuery->add($record);

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                LabelCollection::fromArray($labelCollection),
                $mainImage,
                [
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

            $record = Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                $recordCode,
                $labelCollection,
                $mainImage,
                ValueCollection::fromValues([])
            );

            $this->findRecordIdentifiersForQuery->add($record);

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                LabelCollection::fromArray($labelCollection),
                $mainImage,
                [
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => $firstChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($referenceEntityIdentifier), $i, $firstChannel
                            )
                        ],
                        [
                            'locale' => 'en_US',
                            'channel' => $secondChannel,
                            'data' => sprintf(
                                'Description for %s number %d and channel %s',
                                ucfirst($referenceEntityIdentifier), $i, $secondChannel
                            )
                        ]
                    ],
                    'country' => [
                        [
                            'locale' => null,
                            'channel' => null,
                            'data' => 'italy'
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
     * @When the connector requests all records of the Brand reference entity with the attribute values of the Ecommerce channel
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheAttributeValuesOfTheEcommerceChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages = [];

        $this->recordPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_ecommerce_channel.json'
        );
    }

    /**
     * @Then the PIM returns 3 records of the Brand reference entity with only the attribute values of the Ecommerce channel
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
     * @When the connector requests all records of the Brand reference entity with the attribute values of a non-existent channel
     */
    public function theConnectorRequestAllRecordsOfTheBrandReferenceEntityWithTheAttributeValuesOfANonExistentChannel(): void
    {
        $client = $this->clientFactory->logIn('julia');

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_brand_records_for_non_existent_channel.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the provided channel does not exist
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheProvidedChannelDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->unprocessableEntityResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_brand_records_for_non_existent_channel.json'
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
            $labelCollection = [];
            $mainImage = Image::createEmpty();

            $record = Record::create(
                $recordIdentifier,
                ReferenceEntityIdentifier::fromString('brand'),
                $recordCode,
                $labelCollection,
                $mainImage,
                ValueCollection::fromValues([])
            );

            $this->findRecordIdentifiersForQuery->add($record);

            $connectorRecord = new ConnectorRecord(
                $recordCode,
                LabelCollection::fromArray($labelCollection),
                $mainImage,
                [
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
     * To use only for records with empty images.
     *
     * @Given labels translated in the English and French locale
     */
    public function theRecordsLabelsAreTranslatedInEnglishAndFrench()
    {
        foreach ($this->connectorRecordsByRecordIdentifier as $recordIdentifier => $connectorRecord) {
            $normalizedConnectorRecord = $connectorRecord->normalize();
            $connectorRecord = new ConnectorRecord(
                RecordCode::fromString($normalizedConnectorRecord['code']),
                LabelCollection::fromArray([
                    'en_US' => sprintf('English label for %s', $normalizedConnectorRecord['code']),
                    'fr_FR' => sprintf('French label for %s', $normalizedConnectorRecord['code']),
                ]),
                Image::createEmpty(),
                $normalizedConnectorRecord['values']
            );
            $this->findConnectorRecords->save(RecordIdentifier::fromString($recordIdentifier), $connectorRecord);
        }
    }

    /**
     * @When the connector requests all records of the Brand reference entity with their information in English
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheirInformationInEnglish()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages[1] = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_english_locale.json'
        );
    }

    /**
     * @Then the PIM returns 3 records of the Brand reference entity with the attribute values in English only
     */
    public function thePimReturnsTheRecordsOfTheBrandReferenceEntityWithTheAttributeValuesInEnglishOnly()
    {
        Assert::keyExists($this->recordPages, 1, 'The page 1 has not been loaded');

        $this->webClientHelper->assertJsonFromFile(
            $this->recordPages[1],
            self::REQUEST_CONTRACT_DIR . 'successful_brand_records_for_english_locale.json'
        );
    }

    /**
     * @Then the labels in English only
     */
    public function theLabelsInEnglishOnly()
    {
        Assert::keyExists($this->recordPages, 1, 'The page 1 has not been loaded');

        $responseContent = json_decode($this->recordPages[1]->getContent(), true);

        foreach ($responseContent['_embedded']['items'] as $record) {
            Assert::keyExists($record['labels'], 'en_US', 'All records must have a label in english.');
            Assert::keyNotExists($record['labels'], 'fr_FR', 'All records must not have a label in french.');
        }
    }

    /**
     * @When the connector requests all records of the Brand reference entity with the attribute values of a provided locale that does not exist
     */
    public function theConnectorRequestsAllRecordsOfTheBrandReferenceEntityWithTheAttributesValuesOfAProvidedLocaleThatDoesNotExist()
    {
        $this->findActivatedLocalesByIdentifiers->save(LocaleIdentifier::fromCode('en_US'));
        $client = $this->clientFactory->logIn('julia');

        $this->unprocessableEntityResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_brand_records_for_non_existent_locale.json'
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
}
