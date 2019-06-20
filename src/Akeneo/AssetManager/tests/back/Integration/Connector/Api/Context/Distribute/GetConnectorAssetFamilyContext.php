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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAssetFamilyContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier */
    private $findConnectorAssetFamily;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var null|Response */
    private $existentAssetFamily;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier $findConnectorAssetFamily,
        AssetFamilyRepositoryInterface $assetFamilyRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAssetFamily = $findConnectorAssetFamily;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    /**
     * @Given /^the Brand asset family$/
     */
    public function theBrandAssetFamily(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('brand.jpg')
            ->setKey('5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_brand.jpg');

        $assetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray(['fr_FR' => 'Marque']),
            Image::fromFileInfo($imageInfo)
        );

        $this->findConnectorAssetFamily->save(
            $assetFamilyIdentifier,
            $assetFamily
        );

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When /^the connector requests the Brand asset family$/
     */
    public function theConnectorRequestsTheBrandAssetFamily(): void
    {
        $client = $this->clientFactory->logIn('julia');
        $this->existentAssetFamily = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_brand_asset_family.json"
        );
    }

    /**
     * @Then /^the PIM returns the label and image properties Brand asset family$/
     */
    public function thePIMReturnsTheBrandAssetFamily(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->existentAssetFamily,
            self::REQUEST_CONTRACT_DIR . "successful_brand_asset_family.json"
        );
    }
}
