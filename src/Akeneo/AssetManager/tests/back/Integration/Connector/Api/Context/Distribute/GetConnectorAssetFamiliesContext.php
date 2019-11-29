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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAssetFamilyItems;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

class GetConnectorAssetFamiliesContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAssetFamilyItems */
    private $findConnectorAssetFamily;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var array */
    private $assetFamilyPages;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAssetFamilyItems $findConnectorAssetFamily,
        AssetFamilyRepositoryInterface $assetFamilyRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAssetFamily = $findConnectorAssetFamily;
        $this->assetFamilyRepository = $assetFamilyRepository;
    }

    /**
     * @Given /^7 asset families in the PIM$/
     */
    public function assetFamiliesInThePIM()
    {
        for ($i = 1; $i <= 7; $i++) {
            $rawIdentifier = sprintf('%s_%d', 'asset_family', $i);
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($rawIdentifier);

            $imageInfo = new FileInfo();
            $imageInfo
                ->setOriginalFilename(sprintf('%s.jpg', $rawIdentifier))
                ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

            $productLinkRules = [];
            $transformationCollection = TransformationCollection::create([]);
            if (1 === $i) {
                $productLinkRules = [
                    [
                        'product_selections' => [
                            [
                                'field' => 'sku',
                                'operator' => 'EQUALS',
                                'value' => '{{product_ref}}',
                                'locale' => null,
                            ],
                        ],
                        'assign_assets_to' => [
                            [
                                'attribute' => 'user_instructions',
                                'locale' => '{{locale}}',
                                'mode' => 'replace',
                            ],
                        ],
                    ],
                ];
                $transformationCollection = TransformationCollection::create([
                    Transformation::create(
                        Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
                        Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => null]),
                        OperationCollection::create([ThumbnailOperation::create(['width' => 100, 'height' => 80])]),
                        '1_',
                        '_2'
                    )
                ]);
            }

            $assetFamily = new ConnectorAssetFamily(
                $assetFamilyIdentifier,
                LabelCollection::fromArray(['fr_FR' => 'Marque']),
                Image::fromFileInfo($imageInfo),
                $productLinkRules,
                $transformationCollection
            );

            $this->findConnectorAssetFamily->save(
                $assetFamilyIdentifier,
                $assetFamily
            );

            $assetFamily = AssetFamily::create(
                $assetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            );

            $this->assetFamilyRepository->create($assetFamily);
        }
    }

    /**
     * @When /^the connector requests all asset families of the PIM$/
     */
    public function theConnectorRequestsAllAssetFamiliesOfThePIM()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->assetFamilyPages = [];

        for ($page = 1; $page <= 3; $page++) {
            $this->assetFamilyPages[$page] = $this->webClientHelper->requestFromFile(
                $client,
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_asset_families_page_%d.json",
                    $page
                )
            );
        }
    }

    /**
     * @Then /^the PIM returns the label and image properties of the 7 asset families of the PIM$/
     */
    public function thePIMReturnsTheAssetFamiliesOfThePIM()
    {
        for ($page = 1; $page <= 3; $page++) {
            Assert::keyExists($this->assetFamilyPages, $page, sprintf('The page %d has not been loaded', $page));

            $this->webClientHelper->assertJsonFromFile(
                $this->assetFamilyPages[$page],
                self::REQUEST_CONTRACT_DIR . sprintf(
                    "successful_asset_families_page_%d.json",
                    $page
                )
            );
        }
    }
}
