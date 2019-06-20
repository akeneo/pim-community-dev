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
    private const DISTRIBIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';
    private const COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var null|Response */
    private $notFoundReferenceEntityResponse;

    /** @var null|string */
    private $notFoundReferenceEntityRequestContract;

    /** @var null|Response */
    private $notFoundAttributeForReferenceEntityResponse;

    /** @var null|string */
    private $notFoundAttributeForReferenceEntityRequestContract;

    /** @var null|Response */
    private $notFoundReferenceEntityForAttributeOptionResponse;

    /** @var null|string */
    private $notFoundReferenceEntityForAttributeOptionContract;



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
        $this->notFoundReferenceEntityRequestContract = self::DISTRIBIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_attributes.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When /^the connector requests a given attribute of a non\-existent reference entity$/
     */
    public function theConnectorRequestsAGivenAttributeOfANonExistentReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::DISTRIBIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_reference_entity_for_attribute.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }


    /**
     * @When /^the connector requests a non\-existent attribute of a given reference entity$/
     */
    public function theConnectorRequestsANonExistentAttributeOfAGivenReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::DISTRIBIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_attribute_for_reference_entity.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When /^the connector requests the options of an attribute for a non\-existent reference entity$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeForANonExistentReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::DISTRIBIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "options_for_non_existent_reference_entity.json";
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not exist for the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotExistForTheBrandReferenceEntity()
    {
        $this->webClientHelper->assertJsonFromFile($this->notFoundReferenceEntityResponse, $this->notFoundReferenceEntityRequestContract);
    }


    /**
     * @When /^the connector collects an attribute of a non\-existent reference entity$/
     */
    public function theConnectorCollectsAnAttributeOfANonExistentReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR . 'not_found_reference_entity_for_an_attribute.json';
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }

    /**
     * @When /^the connector collects an attribute option of a non\-existent reference entity$/
     */
    public function theConnectorCollectsAnAttributeOptionOfANonExistentReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundReferenceEntityRequestContract = self::COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR . 'not_found_attribute_for_an_attribute_option.json';
        $this->notFoundReferenceEntityResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundReferenceEntityRequestContract);
    }
}
