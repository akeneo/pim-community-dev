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

namespace Akeneo\AssetManager\Integration\Connector\Api\Context\Distribute;

use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NotFoundAssetFamilyContext implements Context
{
    private const ASSET_REQUEST_CONTRACT_DIR = 'Asset/Connector/Distribute/';
    private const ASSET_FAMILY_REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Distribute/';
    private const DISTRIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';
    private const COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private ?Response $notFoundAssetFamilyResponse = null;

    private ?string $notFoundAssetFamilyRequestContract = null;

    public function __construct(OauthAuthenticatedClientFactory $clientFactory, WebClientHelper $webClientHelper)
    {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
    }

    /**
     * @When the connector requests for a asset for a non-existent asset family
     */
    public function theConnectorRequestsAAssetForANonExistentAssetFamily(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::ASSET_REQUEST_CONTRACT_DIR . "not_found_asset_family_for_a_asset.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When the connector requests for an asset of an existent asset family with the wrong case
     */
    public function theConnectorRequestsAnAssetOfAnExistentAssetFamilyWithTheWrongCase(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::ASSET_REQUEST_CONTRACT_DIR . "not_found_asset_family_with_wrong_case.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile(
            $client,
            $this->notFoundAssetFamilyRequestContract
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family does not exist
     */
    public function thePIMNotifiesAnErrorIndicatingThatTheAssetFamilyDoesNotExist(): void
    {
        $this->webClientHelper->assertJsonFromFile($this->notFoundAssetFamilyResponse, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When the connector requests all the assets for a non-existent asset family
     */
    public function theConnectorRequestsAllTheAssetsForANonExistentAssetFamily(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::ASSET_REQUEST_CONTRACT_DIR . "not_found_asset_family_for_the_list_of_assets.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When the connector requests a non-existent asset family
     */
    public function theConnectorRequestsANonExistentAssetFamily(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::ASSET_FAMILY_REQUEST_CONTRACT_DIR. "not_found_asset_family.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When the connector requests an asset family with wrong case
     */
    public function theConnectorRequestsAnAssetFamilyWithWrongCase(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::ASSET_FAMILY_REQUEST_CONTRACT_DIR. "not_found_asset_family_with_bad_case.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When /^the connector requests the structure of a non\-existent asset family$/
     */
    public function theConnectorRequestsTheStructureOfANonExistentAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::DISTRIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_asset_family_for_attributes.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When /^the connector requests a given attribute of a non\-existent asset family$/
     */
    public function theConnectorRequestsAGivenAttributeOfANonExistentAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::DISTRIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_asset_family_for_attribute.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }


    /**
     * @When /^the connector requests a non\-existent attribute of a given asset family$/
     */
    public function theConnectorRequestsANonExistentAttributeOfAGivenAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::DISTRIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "not_found_attribute_for_asset_family.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When /^the connector requests the options of an attribute for a non\-existent asset family$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeForANonExistentAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::DISTRIBUTE_ATTRIBUTE_REQUEST_CONTRACT_DIR . "options_for_non_existent_asset_family.json";
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not exist for the Brand asset family$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotExistForTheBrandAssetFamily()
    {
        $this->webClientHelper->assertJsonFromFile($this->notFoundAssetFamilyResponse, $this->notFoundAssetFamilyRequestContract);
    }


    /**
     * @When /^the connector collects an attribute of a non\-existent asset family$/
     */
    public function theConnectorCollectsAnAttributeOfANonExistentAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR . 'not_found_asset_family_for_an_attribute.json';
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }

    /**
     * @When /^the connector collects an attribute option of a non\-existent asset family$/
     */
    public function theConnectorCollectsAnAttributeOptionOfANonExistentAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->notFoundAssetFamilyRequestContract = self::COLLECT_ATTRIBUTE_REQUEST_CONTRACT_DIR . 'not_found_attribute_for_an_attribute_option.json';
        $this->notFoundAssetFamilyResponse = $this->webClientHelper->requestFromFile($client, $this->notFoundAssetFamilyRequestContract);
    }
}
