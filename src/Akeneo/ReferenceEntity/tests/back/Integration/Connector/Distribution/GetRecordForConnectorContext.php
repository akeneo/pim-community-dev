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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindRecordForConnectorByReferenceEntityAndCode;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetRecordForConnectorContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Record/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindRecordForConnectorByReferenceEntityAndCode */
    private $findRecordForConnector;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|Response */
    private $existentRecord;

    /** @var null|Response */
    private $nonExistentRecord;

    /** @var null|Response */
    private $nonExistentReferenceEntityResponse;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindRecordForConnectorByReferenceEntityAndCode $findRecordForConnector,
        ReferenceEntityRepositoryInterface $referenceEntityRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findRecordForConnector = $findRecordForConnector;
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    /**
     * @Given /^the ([\S]+) record for the ([\S]+) reference entity$/
     */
    public function theRecordForTheReferenceEntity(string $referenceCode, string $referenceEntityIdentifier): void
    {
        $record = new RecordForConnector(
            RecordCode::fromString($referenceCode),
            LabelCollection::fromArray(['fr_FR' => 'A label']),
            Image::createEmpty(),
            [
                'name' => [
                    [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'data' => 'My Name'
                    ],
                    [
                        'channel' => 'tablet',
                        'locale' => null,
                        'data' => 'My Tablet Name'
                    ]
                ]
            ]
        );
        $this->findRecordForConnector->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($referenceCode),
            $record
        );

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When /^the connector requests the ([\S]+) record for the ([\S]+) reference entity$/
     */
    public function theConnectorRequestsRecordForReferenceEntity(string $referenceCode, string $referenceEntityIdentifier): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->existentRecord = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . sprintf("successful_%s_record.json", strtolower($referenceCode))
        );
    }

    /**
     * @Then /^the PIM returns the ([\S]+) record of the ([\S]+) reference entity$/
     */
    public function thePimReturnsReferenceEntity(string $referenceCode)
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->existentRecord,
            self::REQUEST_CONTRACT_DIR . sprintf("successful_%s_record.json", strtolower($referenceCode))
        );
    }

    /**
     * @Given /^the ([\S]+) reference entity with some records$/
     */
    public function theReferenceEntityWithSomeRecords(string $referenceEntityIdentifier): void
    {
        for ($i = 0; $i < 10 ; $i++) {
            $record = new RecordForConnector(
                RecordCode::fromString('record_code_' . $i),
                LabelCollection::fromArray([]),
                Image::createEmpty(),
                []
            );
            $this->findRecordForConnector->save(
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                RecordCode::fromString('record_code_' . $i),
                $record
            );
        }

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When /^the connector requests for a non-existent record for the ([\S]+) reference entity$/
     */
    public function theConnectorRequestsForANonExistentRecordForTheReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->nonExistentRecord = $this->webClientHelper->requestFromFile($client, self::REQUEST_CONTRACT_DIR . "not_found_record.json");
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the record does not exist
     */
    public function thePIMNotifiesAnErrorIndicatingThatTheRecordDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile($this->nonExistentRecord, self::REQUEST_CONTRACT_DIR . "not_found_record.json");
    }

    /**
     * @Given some reference entities with some records
     */
    public function someReferenceEntitiesWithSomeRecords(): void
    {
        for ($i = 0; $i < 10 ; $i++) {
            for ($j = 0; $j < 10 ; $j++) {
                $record = new RecordForConnector(
                    RecordCode::fromString(sprintf('record_code_%s_%s', $i, $j)),
                    LabelCollection::fromArray([]),
                    Image::createEmpty(),
                    []
                );
                $this->findRecordForConnector->save(
                    ReferenceEntityIdentifier::fromString(sprintf('reference_entity_%s', $i)),
                    RecordCode::fromString(sprintf('record_code_%s_%s', $i, $j)),
                    $record
                );
            }

            $referenceEntity = ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString(sprintf('reference_entity_%s', $i)),
                [],
                Image::createEmpty()
            );

            $this->referenceEntityRepository->create($referenceEntity);
        }
    }

    /**
     * @When the connector requests for a record for a non-existent reference entity
     */
    public function theConnectorRequestsARecordForANonExistentReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->nonExistentReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, self::REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_a_record.json");
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the reference entity does not exist
     */
    public function thePIMNotifiesAnErrorIndicatingThatTheReferenceEntityDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile($this->nonExistentReferenceEntityResponse, self::REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_a_record.json");
    }
}
