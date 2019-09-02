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
use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesPerChannels;
use Akeneo\AssetManager\Common\Fake\InMemoryFindFileDataByFileKey;
use Akeneo\AssetManager\Common\Fake\InMemoryGetAttributeIdentifier;
use Akeneo\AssetManager\Common\Fake\ProductLinkRuleLauncherSpy;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAssetContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Asset/Connector/Collect/';
    private const ASSET_FAMILY_IDENTIFIER = 'frontview';
    private const HOUSE_ASSET_CODE = 'house';
    private const FLOWER_ASSET_CODE = 'flower';
    private const PHONE_ASSET_CODE = 'phone';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var null|Response */
    private $pimResponse;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AssetRepositoryInterface */
    private $assetRepository;

    /** @var null|string */
    private $requestContract;

    /** @var InMemoryChannelExists */
    private $channelExists;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var InMemoryFindActivatedLocalesPerChannels */
    private $activatedLocalesPerChannels;

    /** @var null|Response */
    private $uploadImageResponse;

    /** @var InMemoryFindFileDataByFileKey */
    private $findFileData;

    /** @var InMemoryFileExists */
    private $fileExists;

    /** @var InMemoryGetAttributeIdentifier */
    private $getAttributeIdentifier;

    /** @var ProductLinkRuleLauncherSpy */
    private $productLinkRuleLauncherSpy;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AssetRepositoryInterface $assetRepository,
        InMemoryChannelExists $channelExists,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels,
        InMemoryFindFileDataByFileKey $findFileData,
        InMemoryFileExists $fileExists,
        InMemoryGetAttributeIdentifier $getAttributeIdentifier,
        ProductLinkRuleLauncherSpy $productLinkRuleLauncherSpy
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->assetRepository = $assetRepository;
        $this->channelExists = $channelExists;
        $this->activatedLocales = $activatedLocales;
        $this->activatedLocalesPerChannels = $activatedLocalesPerChannels;
        $this->findFileData = $findFileData;
        $this->fileExists = $fileExists;
        $this->getAttributeIdentifier = $getAttributeIdentifier;
        $this->productLinkRuleLauncherSpy = $productLinkRuleLauncherSpy;
    }

    /**
     * @Given an asset of the Frontview asset family existing in the ERP but not in the PIM
     * @Given an asset of the Frontview asset family existing in the ERP but not in the PIM having a rule template
     */
    public function aAssetOfTheBrandAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_house_asset_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadDescriptionAttribute();
        $this->loadFrontViewAssetFamily();
    }

    /**
     * @When the connector collects this asset from the ERP to synchronize it with the PIM
     */
    public function theConnectorCollectsThisAssetFromTheErpToSynchronizeItWithThePim()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the asset is created in the PIM with the information from the ERP
     */
    public function theAssetIsCreatedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_house_asset_creation.json'
        );

        $houseAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::HOUSE_ASSET_CODE)
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedAsset = Asset::create(
            $houseAsset->getIdentifier(),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House french label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_frontview_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House french description.')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_frontview_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House english description.')
                ),
            ])
        );

        Assert::assertEquals($expectedAsset, $houseAsset);
    }

    /**
     * @Given an asset of the Brand asset family existing in the ERP and the PIM with different information
     */
    public function aAssetOfTheBrandAssetFamilyExistingInTheErpAndThePimWithDifferentInformation()
    {
        $this->requestContract = 'successful_house_asset_update.json';

        $this->loadFrontViewAssetFamily();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadCoverImageAttribute();
        $this->loadFrontViewHouseAsset();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->findFileData->save([
            'filePath'         => '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_house.jpg',
            'originalFilename' => 'house.jpg',
            'size'             => 128,
            'mimeType'         => 'image/jpeg',
            'extension'        => 'jpg'
        ]);
        $this->fileExists->save('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_house.jpg');
    }

    /**
     * @Then the asset is correctly synchronized in the PIM with the information from the ERP
     */
    public function theAssetIsCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_house_asset_update.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $kartellAsset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE)
        );

        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );

        $coverImageInfo = (new FileInfo())
            ->setKey('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_house.jpg')
            ->setOriginalFilename('house.jpg')
            ->setSize(128)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('house.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_house.jpg');

        $expectedKartellAsset = Asset::create(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_frontview_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House english description')
                ),
                Value::create(
                    AttributeIdentifier::fromString('cover_image_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($coverImageInfo)
                ),
            ])
        );

        Assert::assertEquals($expectedKartellAsset, $kartellAsset);
    }

    /**
     * @When the connector collects a asset that has an invalid format
     */
    public function theConnectorCollectsAAssetThatHasAnInvalidFormat()
    {
        $this->loadNameAttribute();
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_house_asset_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_house_asset_for_invalid_format.json'
        );
    }

    /**
     * @When the connector collects a asset whose data does not comply with the business rules
     */
    public function theConnectorCollectsAAssetWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['fr_FR', 'en_US']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $this->loadDescriptionAttribute();
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_house_asset_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_house_asset_for_invalid_data.json'
        );
    }

    /**
     * @Given some assets of the Frontview asset family existing in the ERP but not in the PIM
     */
    public function someAssetsOfTheBrandAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->loadFrontViewAssetFamily();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Given some assets of the Frontview asset family existing in the ERP and in the PIM but with different information
     */
    public function someAssetsOfTheBrandAssetFamilyExistingInTheErpAndInThePimButWithDifferentInformation()
    {
        $this->loadFrontViewHouseAsset();
        $this->loadFrontViewFlowerAsset();
    }

    /**
     * @When the connector collects these assets from the ERP to synchronize them with the PIM
     */
    public function theConnectorCollectsTheseAssetsFromTheErpToSynchronizeThemWithThePim()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_frontview_assets_synchronization.json'
        );
    }

    /**
     * @Then the assets that existed only in the ERP are correctly created in the PIM
     */
    public function theAssetsThatExistedOnlyInTheErpAreCorrectlyCreatedInThePim()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_frontview_assets_synchronization.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $phoneAsset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString(self::PHONE_ASSET_CODE)
        );

        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedPhoneAsset = Asset::create(
            $phoneAsset->getIdentifier(),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::PHONE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Phone label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Phone name')
                )
            ])
        );

        Assert::assertEquals($expectedPhoneAsset, $phoneAsset);
    }

    /**
     * @Then the assets existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
     */
    public function theAssetsExistingBothInTheErpAndThePimAreCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('house.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_house.jpg');

        $expectedHouseAsset = Asset::create(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_frontview_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House english description')
                )
            ])
        );

        $houseAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::HOUSE_ASSET_CODE)
        );

        Assert::assertEquals($expectedHouseAsset, $houseAsset);

        $expectedFlowerAsset = Asset::create(
            AssetIdentifier::fromString('frontview_flower_fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::FLOWER_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Flower updated english label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated Flower english name')
                )
            ])
        );

        $flowerAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::FLOWER_ASSET_CODE)
        );

        Assert::assertEquals($expectedFlowerAsset, $flowerAsset);
    }

    /**
     * @When the connector collects assets from the ERP among which some assets have data that do not comply with the business rules
     */
    public function theConnectorCollectsAssetsFromTheErpAmongWhichSomeAssetsHaveDataThatDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'collect_frontview_assets_with_unprocessable_assets.json'
        );
    }

    /**
     * @Then the PIM notifies the connector which assets have data that do not comply with the business rules and what are the errors
     */
    public function thePimNotifiesTheConnectorWhichAssetsHaveDataThatDoNotComplyWithTheBusinessRulesAndWhatAreTheErrors()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'collect_frontview_assets_with_unprocessable_assets.json'
        );
    }

    /**
     * @When the connector collects a number of assets exceeding the maximum number of assets in one request
     */
    public function theConnectorCollectsANumberOfAssetsExceedingTheMaximumNumberOfAssetsInOneRequest()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'too_many_assets_to_process.json'
        );
    }

    /**
     * @Then the PIM notifies the connector that there were too many assets to collect in one request
     */
    public function thePimNotifiesTheConnectorThatThereWereTooManyAssetsToCollectInOneRequest()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'too_many_assets_to_process.json'
        );
    }

    /**
     * @Given /^the Kartell asset of the Brand asset family without any media file$/
     */
    public function theKartellAssetOfTheBrandAssetFamilyWithoutAnyMediaFile()
    {
        $this->loadFrontViewAssetFamily();
        $this->loadCoverImageAttribute();
        $this->loadFrontViewHouseAsset();
    }

    /**
     * @When /^the connector collects a media file for the Kartell asset from the DAM to synchronize it with the PIM$/
     */
    public function theConnectorCollectsAMediaFileForTheKartellAssetFromTheDAMToSynchronizeItWithThePIM()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->uploadImageResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_image_upload.json"
        );
    }

    /**
     * @Then /^the Kartell asset is correctly synchronized with the uploaded media file$/
     */
    public function theKartellAssetIsCorrectlySynchronizedWithTheUploadedMediaFile()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->uploadImageResponse,
            self::REQUEST_CONTRACT_DIR ."successful_image_upload.json"
        );
    }

    private function loadFrontViewAssetFamily(): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'Front view'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function loadDescriptionAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadNameAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadCoverImageAttribute(): void
    {
        $image = ImageAttribute::create(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, 'cover_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('cover_image'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($image);
    }

    private function loadFrontViewHouseAsset(): void
    {
        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('house.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_house.jpg');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );

        $asset = Asset::create(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House English label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House french name')
                )
            ])
        );

        $this->assetRepository->create($asset);
    }

    private function loadFrontViewFlowerAsset(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $asset = Asset::create(
            AssetIdentifier::fromString('frontview_flower_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::FLOWER_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Flower')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Flower name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_frontview_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Flower description')
                )
            ])
        );

        $this->assetRepository->create($asset);
    }

    /**
     * @Given /^a job runs to automatically link to products the newly created asset according to the rule template$/
     */
    public function aJobRunsToAutomaticallyLinkToProductsTheNewlyCreatedAssetAccordingToTheRuleTemplate()
    {
        $this->productLinkRuleLauncherSpy->assertHasRunForAsset(self::ASSET_FAMILY_IDENTIFIER, self::PHONE_ASSET_CODE);
    }

    /**
     * @Given /^a job runs to automatically link it to products according to the rule template$/
     */
    public function aJobRunsToAutomaticallyLinkItToProductsAccordingToTheRuleTemplate()
    {
        $this->productLinkRuleLauncherSpy->assertHasRunForAsset(self::ASSET_FAMILY_IDENTIFIER, self::HOUSE_ASSET_CODE);
    }

    /**
     * @Given /^a single job runs to automatically link to products the newly created asset according to the rule template$/
     */
    public function aSingleJobRunsToAutomaticallyLinkToProductsTheNewlyCreatedAssetAccordingToTheRuleTemplate()
    {
        $this->productLinkRuleLauncherSpy->assertHasRunForAssetsInSameLaunch(
            self::ASSET_FAMILY_IDENTIFIER,
            [self::HOUSE_ASSET_CODE, self::PHONE_ASSET_CODE, self::FLOWER_ASSET_CODE]
        );
    }
}
