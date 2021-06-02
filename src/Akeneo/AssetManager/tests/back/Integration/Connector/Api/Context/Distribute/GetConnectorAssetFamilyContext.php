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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAssetFamilyContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Distribute/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private InMemoryFindConnectorAssetFamilyByAssetFamilyIdentifier $findConnectorAssetFamily;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ?Response $existentAssetFamily = null;

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
                ]
            ]
        ];

        $connectorTransformations = new ConnectorTransformationCollection([
            new ConnectorTransformation(
                TransformationLabel::fromString('the_label'),
                Source::createFromNormalized(['attribute' => 'main', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => null]),
                OperationCollection::create([ThumbnailOperation::create(['width' => 100, 'height' => 80])]),
                null,
                '_2'
            )
        ]);

        $assetFamily = new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray(['fr_FR' => 'Marque']),
            Image::fromFileInfo($imageInfo),
            $productLinkRules,
            $connectorTransformations,
            new NullNamingConvention(),
            null
        );

        $this->findConnectorAssetFamily->save(
            $assetFamilyIdentifier,
            $assetFamily
        );

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['fr_FR' => 'Marque'],
            Image::fromFileInfo($imageInfo),
            RuleTemplateCollection::createFromProductLinkRules($productLinkRules)
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
     * @Then /^the PIM returns the label, media_file properties and rule templates of Brand asset family$/
     */
    public function thePIMReturnsTheBrandAssetFamily(): void
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->existentAssetFamily,
            self::REQUEST_CONTRACT_DIR . "successful_brand_asset_family.json"
        );
    }
}
