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

use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Behat\Behat\Context\Context;
use PhpSpec\Exception\Example\PendingException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NotFoundReferenceEntityContext implements Context
{
    private const RECORD_REQUEST_CONTRACT_DIR = 'Record/Connector/Distribute/';
    private const REFERENCE_ENTITY_REQUEST_CONTRACT_DIR = 'ReferenceEntity/Connector/Distribute/';
    private const ATTRIBUTE_REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var null|Response */
    private $notFoundReferenceEntityResponse;

    /** @var null|string */
    private $notFoundReferenceEntityRequestContract;

    public function __construct(OauthAuthenticatedClientFactory $clientFactory, WebClientHelper $webClientHelper)
    {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
    }

    /**
     * @When the connector requests for a record for a non-existent reference entity
     */
    public function theConnectorRequestsARecordForANonExistentReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::RECORD_REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_a_record.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the reference entity does not exist
     */
    public function thePIMNotifiesAnErrorIndicatingThatTheReferenceEntityDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile($this->notFoundReferenceEntityResponse, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When the connector requests all the records for a non-existent reference entity
     */
    public function theConnectorRequestsAllTheRecordsForANonExistentReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::RECORD_REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_the_list_of_records.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When the connector requests a non-existent reference entity
     */
    public function theConnectorRequestsANonExistentReferenceEntity(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::REFERENCE_ENTITY_REQUEST_CONTRACT_DIR. "not_found_reference_entity.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When /^the connector requests the structure of a non\-existent reference entity$/
     */
    public function theConnectorRequestsTheStructureOfANonExistentReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_attributes.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }
}
