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

namespace Akeneo\AssetManager\Integration\Connector\Api\Context\Collect;

use Akeneo\AssetManager\Common\Fake\InMemoryChannelExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryGetAssetCollectionTypeAdapter;
use Akeneo\AssetManager\Common\Fake\InMemoryGetAttributeIdentifier;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAssetFamilyContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'AssetFamily/Connector/Collect/';
    private const VALID_EXTENSIONS = ['gif', 'jfif', 'jif', 'jpeg', 'jpg', 'pdf', 'png', 'psd', 'tif', 'tiff'];

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var InMemoryGetAttributeIdentifier */
    private $getAttributeIdentifier;

    /** @var InMemoryGetAssetCollectionTypeAdapter */
    private $findAssetCollectionTypeACL;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        InMemoryGetAttributeIdentifier $getAttributeIdentifier,
        InMemoryGetAssetCollectionTypeAdapter $findAssetCollectionTypeACL,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->findAssetCollectionTypeACL = $findAssetCollectionTypeACL;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given the Frontview asset family existing in the ERP but not in the PIM
     */
    public function theFrontviewAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->findAssetCollectionTypeACL->stubWith('frontview');
        $this->requestContract = 'successful_frontview_asset_family_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
    }

    /**
     * @When the connector collects the properties of the Brand asset family from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThePropertiesOfTheBrandAssetFamilyFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the asset family is created with its properties in the PIM with the information from the ERP
     */
    public function theAssetFamilyIsCreated()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_frontview_asset_family_creation.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('frontview');
        $attributeAsLabelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );
        $ruleTemplate = $this->getExpectedRuleTemplate();

        $frontview = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('frontview'));
        $expectedFrontview = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [
                'en_US' => 'Frontview english label',
                'fr_FR' => 'Frontview french label',
            ],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($attributeAsLabelIdentifier),
            AttributeAsMainMediaReference::fromAttributeIdentifier($attributeAsMainMediaIdentifier),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );

        Assert::assertEquals($expectedFrontview, $frontview);
    }

    /**
     * @Given the Brand asset family existing in the ERP and the PIM with different properties
     */
    public function theBrandAssetFamilyExistingInTheErpAndInThePimWithDifferentProperties()
    {
        $this->findAssetCollectionTypeACL->stubWith('brand');
        $this->requestContract = 'successful_brand_asset_family_update.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->loadMediaFileAttribute('brand', 'main_image', 'Main image', 1);
        $this->loadMediaFileAttribute('brand', 'thumbnail', 'Thumbnail image', 2);

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector collects the Brand asset family from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsTheBrandAssetFamilyFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the properties of the asset family are correctly synchronized in the PIM with the information from the ERP
     */
    public function thePropertiesOfTheAssetFamilyAreCorrectlySynchornizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_asset_family_update.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $attributeAsLabelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );
        $ruleTemplate = $this->getExpectedRuleTemplate();

        $brand = $this->assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('brand'));
        $expectedBrand = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [
                'en_US' => 'Brand english label',
                'fr_FR' => 'Brand french label',
            ],
            Image::createEmpty(),
            AttributeAsLabelReference::fromAttributeIdentifier($attributeAsLabelIdentifier),
            AttributeAsMainMediaReference::fromAttributeIdentifier($attributeAsMainMediaIdentifier),
            RuleTemplateCollection::createFromProductLinkRules([$ruleTemplate])
        );
        $expectedBrand = $expectedBrand->withTransformationCollection(TransformationCollection::create([
            Transformation::create(
                Source::createFromNormalized(['attribute' => 'main_image', 'channel'=> null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'thumbnail', 'channel'=> null, 'locale' => null]),
                OperationCollection::create([
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                ]),
                '1_',
                '_2'
            ),
        ]));

        Assert::assertEquals($brand, $expectedBrand);
    }

    /**
     * @Given some asset families
     */
    public function someAssetFamilies()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('de_DE'));

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [
                'en_US' => 'It is an english label'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When the connector collects an asset family that has an invalid format
     */
    public function collectAAssetFamilyWithAnInvalidFormat()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetFamilyHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects an asset family whose data does not comply with the business rules
     */
    public function theConnectorCollectsAAssetFamilyWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetFamilyHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_data.json'
        );
    }

    /**
     * @return array
     */
    private function getExpectedRuleTemplate(): array
    {
        return [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => 'equals',
                    'value'    => '123134124123'
                ],
                [
                    'field'    => 'enabled',
                    'operator' => '=',
                    'value'    => true
                ],
                [
                    'field'    => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value'    => ['shoes', 'tshirts']
                ],
                [
                    'field'    => 'description',
                    'operator' => 'CONTAINS',
                    'value'    => 'shoes',
                    'locale'   => null,
                    'channel'  => 'ecommerce',
                ],
                [
                    'field'    => 'color',
                    'operator' => '=',
                    'value'    => 'yellow',
                    'locale'   => 'en_US',
                    'channel'  => null,
                ],
            ],
            'assign_assets_to'    => [
                [
                    'mode'  => 'add',
                    'attribute' => 'product_asset_collection',
                ]
            ]
        ];
    }

    private function loadMediaFileAttribute(string $assetFamilyIdentifier, string $code, string $label, int $order): void
    {
        $name = MediaFileAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $code, 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code),
            LabelCollection::fromArray(['en_US' => $label]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(self::VALID_EXTENSIONS),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($name);
    }
}
