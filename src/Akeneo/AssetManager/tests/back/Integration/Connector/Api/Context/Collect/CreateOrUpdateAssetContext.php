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
        InMemoryGetAttributeIdentifier $getAttributeIdentifier
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
    }

    /**
     * @Given a asset of the Brand asset family existing in the ERP but not in the PIM
     */
    public function aAssetOfTheBrandAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_kartell_asset_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadDescriptionAttribute();
        $this->loadBrandAssetFamily();
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
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_asset_creation.json'
        );

        $kartellAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('kartell')
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedAsset = Asset::create(
            $kartellAsset->getIdentifier(),
            $assetFamilyIdentifier,
            AssetCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french description.')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description.')
                ),
            ])
        );

        Assert::assertEquals($expectedAsset, $kartellAsset);
    }

    /**
     * @Given a asset of the Brand asset family existing in the ERP and the PIM with different information
     */
    public function aAssetOfTheBrandAssetFamilyExistingInTheErpAndThePimWithDifferentInformation()
    {
        $this->requestContract = 'successful_kartell_asset_update.json';

        $this->loadBrandAssetFamily();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadCoverImageAttribute();
        $this->loadBrandKartellAsset();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->findFileData->save([
            'filePath'         => '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg',
            'originalFilename' => 'kartell_cover.jpg',
            'size'             => 128,
            'mimeType'         => 'image/jpeg',
            'extension'        => 'jpg'
        ]);
        $this->fileExists->save('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg');
    }

    /**
     * @Then the asset is correctly synchronized in the PIM with the information from the ERP
     */
    public function theAssetIsCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_kartell_asset_update.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $kartellAsset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString('kartell')
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
            ->setKey('2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_kartell_cover.jpg')
            ->setOriginalFilename('kartell_cover.jpg')
            ->setSize(128)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $expectedKartellAsset = Asset::create(
            AssetIdentifier::fromString('brand_kartell_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description')
                ),
                Value::create(
                    AttributeIdentifier::fromString('cover_image_brand_fingerprint'),
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
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_asset_for_invalid_format.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset has an invalid format
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_asset_for_invalid_format.json'
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
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_asset_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset has data that does not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_kartell_asset_for_invalid_data.json'
        );
    }

    /**
     * @Given some assets of the Brand asset family existing in the ERP but not in the PIM
     */
    public function someAssetsOfTheBrandAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->loadBrandAssetFamily();
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
    }

    /**
     * @Given some assets of the Brand asset family existing in the ERP and in the PIM but with different information
     */
    public function someAssetsOfTheBrandAssetFamilyExistingInTheErpAndInThePimButWithDifferentInformation()
    {
        $this->loadBrandKartellAsset();
        $this->loadBrandLexonAsset();
    }

    /**
     * @When the connector collects these assets from the ERP to synchronize them with the PIM
     */
    public function theConnectorCollectsTheseAssetsFromTheErpToSynchronizeThemWithThePim()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_synchronization.json'
        );
    }

    /**
     * @Then the assets that existed only in the ERP are correctly created in the PIM
     */
    public function theAssetsThatExistedOnlyInTheErpAreCorrectlyCreatedInThePim()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_brand_assets_synchronization.json'
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $fatboyAsset = $this->assetRepository->getByAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AssetCode::fromString('fatboy')
        );

        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $expectedFatboyAsset = Asset::create(
            $fatboyAsset->getIdentifier(),
            $assetFamilyIdentifier,
            AssetCode::fromString('fatboy'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Fatboy label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Fatboy name')
                )
            ])
        );

        Assert::assertEquals($expectedFatboyAsset, $fatboyAsset);
    }

    /**
     * @Then the assets existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
     */
    public function theAssetsExistingBothInTheErpAndThePimAreCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
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
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $expectedKartellAsset = Asset::create(
            AssetIdentifier::fromString('brand_kartell_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english label')
                ),
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell updated french label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell updated english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english description')
                )
            ])
        );

        $kartellAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('kartell')
        );

        Assert::assertEquals($expectedKartellAsset, $kartellAsset);

        $expectedLexonAsset = Asset::create(
            AssetIdentifier::fromString('brand_lexon_fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('lexon'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon updated english label')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Updated Lexon english name')
                )
            ])
        );

        $lexonAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('lexon')
        );

        Assert::assertEquals($expectedLexonAsset, $lexonAsset);
    }

    /**
     * @When the connector collects assets from the ERP among which some assets have data that do not comply with the business rules
     */
    public function theConnectorCollectsAssetsFromTheErpAmongWhichSomeAssetsHaveDataThatDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_assets_with_unprocessable_assets.json'
        );
    }

    /**
     * @Then the PIM notifies the connector which assets have data that do not comply with the business rules and what are the errors
     */
    public function thePimNotifiesTheConnectorWhichAssetsHaveDataThatDoNotComplyWithTheBusinessRulesAndWhatAreTheErrors()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'collect_brand_assets_with_unprocessable_assets.json'
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
        $this->loadBrandAssetFamily();
        $this->loadCoverImageAttribute();
        $this->loadBrandKartellAsset();
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

    private function loadBrandAssetFamily(): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            ['en_US' => 'Brand'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function loadDescriptionAttribute(): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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
            AttributeIdentifier::create('brand', 'name', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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
            AttributeIdentifier::create('brand', 'cover_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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

    private function loadBrandKartellAsset(): void
    {
        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('kartell.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $mainImageIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('image')
        );

        $asset = Asset::create(
            AssetIdentifier::fromString('brand_kartell_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString('kartell'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell English label')
                ),
                Value::create(
                    $mainImageIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo)
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Kartell english name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Kartell french name')
                )
            ])
        );

        $this->assetRepository->create($asset);
    }

    private function loadBrandLexonAsset(): void
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $labelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );

        $asset = Asset::create(
            AssetIdentifier::fromString('brand_lexon_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString('lexon'),
            ValueCollection::fromValues([
                Value::create(
                    $labelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon')
                ),
                Value::create(
                    AttributeIdentifier::fromString('name_brand_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon name')
                ),
                Value::create(
                    AttributeIdentifier::fromString('description_brand_fingerprint'),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Lexon description')
                )
            ])
        );

        $this->assetRepository->create($asset);
    }
}
