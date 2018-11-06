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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindRecordsForConnectorByReferenceEntity;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class GetRecordsForConnectorContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Record/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindRecordsForConnectorByReferenceEntity */
    private $findRecordsForConnector;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var array */
    private $recordPages;

    /** @var int */
    private $numberOfRecordsPerPage;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindRecordsForConnectorByReferenceEntity $findRecordsForConnector,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository,
        int $numberOfRecordsPerPage
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findRecordsForConnector = $findRecordsForConnector;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
        $this->recordPages = [];

        Assert::greaterThan($numberOfRecordsPerPage, 0);
        $this->numberOfRecordsPerPage = $numberOfRecordsPerPage;
    }

    /**
     * @Given /^([\d]+) records for the ([\S]+) reference entity$/
     */
    public function theRecordsForTheReferenceEntity(int $numberOfRecords, string $referenceEntityIdentifier): void
    {
        $referenceEntityIdentifier = strtolower($referenceEntityIdentifier);

        for ($i = 0; $i < $numberOfRecords; $i++) {
            $recordNumber = $i + 10; // To facilitate sorting
            $recordCode = sprintf('%s_%d', $referenceEntityIdentifier, $recordNumber);
            $mainImageInfo = new FileInfo();
            $mainImageInfo
                ->setOriginalFilename(sprintf('%s_image.jpg', $recordCode))
                ->setKey(sprintf('test/%s_image.jpg', $recordCode));

            $record = new RecordForConnector(
                RecordCode::fromString($recordCode),
                LabelCollection::fromArray([
                    'en_US' => sprintf('%s number %d', ucfirst($referenceEntityIdentifier), $recordNumber)
                ]),
                Image::fromFileInfo($mainImageInfo),
                [
                    'description' => [
                        [
                            'locale' => 'en_US',
                            'channel' => null,
                            'data' => sprintf('%s example %d', ucfirst($referenceEntityIdentifier), $recordNumber)
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

            $this->findRecordsForConnector->save(
                ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                RecordCode::fromString($recordCode),
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
     * @When /^the connector requests all records of the ([\S]+) reference entity$/
     */
    public function theConnectorRequestsAllRecordsOfTheReferenceEntity(string $referenceEntityIdentifier): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->recordPages = [];

        for ($page = 1; $page <= 3; $page++) {
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
     * @Then /^the PIM returns the ([\d]+) records of the ([\S]+) reference entity$/
     */
    public function thePimReturnsTheRecordsOfTheReferenceEntity(
        int $numberOfRecords,
        string $referenceEntityIdentifier
    ): void {
        $pageMax = (int) ceil($numberOfRecords / $this->numberOfRecordsPerPage);

        for ($page = 1; $page <= $pageMax; $page++) {
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
}
