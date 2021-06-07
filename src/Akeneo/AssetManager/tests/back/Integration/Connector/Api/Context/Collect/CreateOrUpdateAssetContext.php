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

use Akeneo\AssetManager\Common\Fake\ComputeTransformationFromAssetIdentifiersLauncherSpy;
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
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
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
    private const VALID_EXTENSIONS = ['gif', 'jfif', 'jif', 'jpeg', 'jpg', 'pdf', 'png', 'psd', 'tif', 'tiff'];

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ?Response $pimResponse = null;

    private AttributeRepositoryInterface $attributeRepository;

    private AssetRepositoryInterface $assetRepository;

    private ?string $requestContract = null;

    private InMemoryChannelExists $channelExists;

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    private InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels;

    private ?Response $uploadImageResponse = null;

    private InMemoryFindFileDataByFileKey $findFileData;

    private InMemoryFileExists $fileExists;

    private InMemoryGetAttributeIdentifier $getAttributeIdentifier;

    private ProductLinkRuleLauncherSpy $productLinkRuleLauncherSpy;

    private ComputeTransformationFromAssetIdentifiersLauncherSpy $computeTransformationLauncherSpy;

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
        ProductLinkRuleLauncherSpy $productLinkRuleLauncherSpy,
        ComputeTransformationFromAssetIdentifiersLauncherSpy $computeTransformationLauncherSpy
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
        $this->computeTransformationLauncherSpy = $computeTransformationLauncherSpy;
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
     * @Given an asset of the PresentationView asset family existing in the ERP but not in the PIM
     */
    public function aAssetOfTheBrandAssetFamilyPresentationViewExistingInTheErpButNotInThePim()
    {
        $this->requestContract = 'successful_building_asset_creation.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadMediaFileAttribute('PresentationView', 'main_image', 'Main Image', 1, '2/4/3/7/24378761474c58aeee26016ee881b3b15069de52_house.jpg');
        $this->loadMediaFileAttribute('PresentationView', 'thumbnail', 'Thumbnail', 2);
        $this->loadPresentationViewAssetFamily('PresentationView');
    }

    /**
     * @Given an asset of the PresentationView asset family existing in the ERP but not in the PIM with naming convention
     */
    public function anAssetOfTheBrandAssetFamilyPresentationViewExistingInTheErpButNotInThePimWithNamingConvention()
    {
        $this->requestContract = 'successful_building_asset_creation_with_naming_convention.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadMediaFileAttribute('PresentationView', 'main_image', 'Main Image', 1, 'path/to/house_12.jpg', 'house_12.jpg');
        $this->loadMediaFileAttribute('PresentationView', 'thumbnail', 'Thumbnail', 2);
        $this->loadTextAttribute('PresentationView', 'title', 3);
        $this->loadNumberAttribute('PresentationView', 'length', 4);
        $this->loadPresentationViewAssetFamily('PresentationView', NamingConvention::createFromNormalized([
            'source' => ['property' => 'main_image', 'channel' => null, 'locale' => null],
            'pattern' => '/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)/',
            'abort_asset_creation_on_error' => true,
        ]));
    }

    /**
     * @Given an asset of the PresentationView asset family existing in the ERP but not in the PIM with naming convention execution failure
     */
    public function anAssetOfTheBrandAssetFamilyPresentationViewExistingInTheErpButNotInThePimWithNamingConventionExecutionFailure()
    {
        $this->requestContract = 'unprocessable_entity_building_asset_for_naming_convention_failure.json';

        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->activatedLocalesPerChannels->save('ecommerce', ['en_US', 'fr_FR']);
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->loadMediaFileAttribute('PresentationView', 'main_image', 'Main Image', 1, 'path/to/house_12.jpg', 'house_12.jpg');
        $this->loadMediaFileAttribute('PresentationView', 'thumbnail', 'Thumbnail', 2);
        $this->loadNumberAttribute('PresentationView', 'length', 3);
        $this->loadPresentationViewAssetFamily('PresentationView', NamingConvention::createFromNormalized([
            'source' => ['property' => 'main_image', 'channel' => null, 'locale' => null],
            'pattern' => '/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)/',
            'abort_asset_creation_on_error' => true,
        ]));
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

        $expectedAsset = Asset::fromState(
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
            ]),
            $houseAsset->getCreatedAt(),
            $houseAsset->getUpdatedAt()
        );

        Assert::assertEquals($expectedAsset, $houseAsset);
    }

    /**
     * @Then the asset is created in the PIM from the request :arg1
     */
    public function theAssetIsCreatedInThePimFromTheRequest(string $filename)
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $filename
        );
    }

    /**
     * @Given an asset of the Brand asset family existing in the ERP and the PIM with different information
     */
    public function aAssetOfTheBrandAssetFamilyExistingInTheErpAndThePimWithDifferentInformation()
    {
        $this->requestContract = 'successful_house_asset_update.json';

        $this->loadFrontViewAssetFamily(true);
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadCoverMediaFileAttribute();
        $this->loadMediaFileAttribute('frontview', 'thumbnail', 'Thumbnail', 6);
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
            'extension'        => 'jpg',
            'updatedAt'        => '2019-11-22T15:16:21+0000',
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

        $attributeAsLabelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
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

        $expectedKartellAsset = Asset::fromState(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $attributeAsLabelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House updated english label')
                ),
                Value::create(
                    $attributeAsLabelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House updated french label')
                ),
                Value::create(
                    $attributeAsMainMediaIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
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
                    FileData::createFromFileinfo($coverImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                ),
            ]),
            $kartellAsset->getCreatedAt(),
            $kartellAsset->getUpdatedAt()
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
     * @Then the PIM notifies the connector about an error indicating that the naming convention failed because of missing attribute
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheNamingConventionFailedBecauseOfMissingAttribute()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_entity_building_asset_for_naming_convention_failure.json'
        );
    }

    /**
     * @Given some assets of the Frontview asset family existing in the ERP but not in the PIM
     */
    public function someAssetsOfTheBrandAssetFamilyExistingInTheErpButNotInThePim()
    {
        $this->loadFrontViewAssetFamily(false, NamingConvention::createFromNormalized([
            'source' => ['property' => 'code', 'channel' => null, 'locale' => null],
            'pattern' => '/(?P<title>.+)/',
            'abort_asset_creation_on_error' => true,
        ]));
        $this->loadDescriptionAttribute();
        $this->loadNameAttribute();
        $this->loadTextAttribute(self::ASSET_FAMILY_IDENTIFIER, 'title', 5);
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

        $expectedPhoneAsset = Asset::fromState(
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
                ),
                // Created by naming convention execution
                Value::create(
                    AttributeIdentifier::fromString('title_frontview_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    TextData::fromString(self::PHONE_ASSET_CODE)
                )
            ]),
            $phoneAsset->getCreatedAt(),
            $phoneAsset->getUpdatedAt()
        );

        Assert::assertEquals($expectedPhoneAsset, $phoneAsset);
    }

    /**
     * @Then the assets existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
     */
    public function theAssetsExistingBothInTheErpAndThePimAreCorrectlySynchronizedInThePimWithTheInformationFromTheErp()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER);
        $attributeAsLabelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $mainImageInfo = new FileInfo();
        $mainImageInfo
            ->setOriginalFilename('house.jpg')
            ->setKey('0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_house.jpg');

        $houseAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::HOUSE_ASSET_CODE)
        );

        $expectedHouseAsset = Asset::fromState(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $attributeAsLabelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House updated english label')
                ),
                Value::create(
                    $attributeAsLabelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('House updated french label')
                ),
                Value::create(
                    $attributeAsMainMediaIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
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
            ]),
            $houseAsset->getCreatedAt(),
            $houseAsset->getUpdatedAt()
        );

        Assert::assertEquals($expectedHouseAsset, $houseAsset);

        $flowerAsset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::FLOWER_ASSET_CODE)
        );

        $expectedFlowerAsset = Asset::fromState(
            AssetIdentifier::fromString('frontview_flower_fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AssetCode::fromString(self::FLOWER_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $attributeAsLabelIdentifier,
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
            ]),
            $flowerAsset->getCreatedAt(),
            $flowerAsset->getUpdatedAt()
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
        $this->loadCoverMediaFileAttribute();
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
            self::REQUEST_CONTRACT_DIR . "successful_image_upload.json"
        );
    }

    /**
     * @Then /^the Kartell asset is correctly synchronized with the uploaded media file$/
     */
    public function theKartellAssetIsCorrectlySynchronizedWithTheUploadedMediaFile()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->uploadImageResponse,
            self::REQUEST_CONTRACT_DIR . "successful_image_upload.json"
        );
    }

    private function loadFrontViewAssetFamily(bool $withTransformation = false, NamingConventionInterface $namingConvention = null): void
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            ['en_US' => 'Front view'],
            Image::createEmpty(),
            RuleTemplateCollection::createFromProductLinkRules(
                [
                    [
                        'product_selections' => [
                            [
                                'field' => '{{category_field}}',
                                'operator' => Operators::EQUALS,
                                'value' => '{{category}}',
                            ],
                        ],
                        'assign_assets_to' => [
                            [
                                'mode' => 'add',
                                'attribute' => '{{product_multiple_link}}',
                            ],
                        ],
                    ],
                ]
            )
        );
        if ($withTransformation) {
            $assetFamily = $assetFamily->withTransformationCollection(
                TransformationCollection::create([
                    Transformation::create(
                        TransformationLabel::fromString('label'),
                        Source::createFromNormalized(['attribute' => 'cover_image', 'channel' => null, 'locale' => null]),
                        Target::createFromNormalized(['attribute' => 'thumbnail', 'channel' => null, 'locale' => null]),
                        OperationCollection::create([
                            ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                        ]),
                        'pre',
                        null,
                        new \DateTime()
                    ),
                ])
            );
        }
        if (null !== $namingConvention) {
            $assetFamily->updateNamingConvention($namingConvention);
        }

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function loadPresentationViewAssetFamily(
        string $assetFamilyIdentifier,
        NamingConventionInterface $namingConvention = null
    ): void {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            ['en_US' => 'Presentation view'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamily = $assetFamily->withTransformationCollection(
            TransformationCollection::create([
                Transformation::create(
                    TransformationLabel::fromString('label'),
                    Source::createFromNormalized(['attribute' => 'main_image', 'channel' => null, 'locale' => null]),
                    Target::createFromNormalized(['attribute' => 'thumbnail', 'channel' => null, 'locale' => null]),
                    OperationCollection::create([
                        ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                    ]),
                    'pre',
                    null,
                    new \DateTime()
                ),
            ])
        );
        if (null !== $namingConvention) {
            $assetFamily->updateNamingConvention($namingConvention);
        }

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
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadTextAttribute(string $assetFamilyIdentifier, string $code, int $order): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create($assetFamilyIdentifier, $code, 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code),
            LabelCollection::fromArray(['en_US' => $code]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadNumberAttribute(string $assetFamilyIdentifier, string $code, int $order): void
    {
        $name = NumberAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $code, 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code),
            LabelCollection::fromArray(['en_US' => $code]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(true),
            AttributeLimit::limitless(),
            AttributeLimit::limitless()
        );

        $this->attributeRepository->create($name);
    }

    private function loadMediaFileAttribute(string $assetFamilyIdentifier, string $code, string $label, int $order, string $filePath = null, string $filename = 'image.jpg'): void
    {
        $name = MediaFileAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $code, 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code),
            LabelCollection::fromArray(['en_US' => $label]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(self::VALID_EXTENSIONS),
            MediaType::fromString(MediaType::IMAGE)
        );

        if (null !== $filePath) {
            $this->findFileData->save([
                'filePath' => $filePath,
                'originalFilename' => $filename,
                'size' => 128,
                'mimeType' => 'image/jpeg',
                'extension' => 'jpg',
                'updatedAt' => '2019-11-22T15:16:21+0000',
            ]);
            $this->fileExists->save($filePath);
        }

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
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
    }

    private function loadCoverMediaFileAttribute(): void
    {
        $image = MediaFileAttribute::create(
            AttributeIdentifier::create(self::ASSET_FAMILY_IDENTIFIER, 'cover_image', 'fingerprint'),
            AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_IDENTIFIER),
            AttributeCode::fromString('cover_image'),
            LabelCollection::fromArray(['en_US' => 'Cover Image']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
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
        $attributeAsLabelIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString('label')
        );
        $attributeAsMainMediaIdentifier = $this->getAttributeIdentifier->withAssetFamilyAndCode(
            $assetFamilyIdentifier,
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
        );

        $asset = Asset::create(
            AssetIdentifier::fromString('frontview_house_fingerprint'),
            $assetFamilyIdentifier,
            AssetCode::fromString(self::HOUSE_ASSET_CODE),
            ValueCollection::fromValues([
                Value::create(
                    $attributeAsLabelIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('House English label')
                ),
                Value::create(
                    $attributeAsMainMediaIdentifier,
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($mainImageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
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
     * @Then /^a job runs to automatically link it to products according to the rule template$/
     */
    public function aJobRunsToAutomaticallyLinkItToProductsAccordingToTheRuleTemplate()
    {
        $this->productLinkRuleLauncherSpy->assertHasRunForAsset(self::ASSET_FAMILY_IDENTIFIER, self::HOUSE_ASSET_CODE);
    }

    /**
     * @Then a job runs to automatically compute the transformations on the asset code :arg1 in asset family :arg2
     */
    public function aJobRunsToAutomaticallyComputeTheTransformationOnTheAsset(
        string $assetcode,
        string $assetFamilyIdentifier
    ) {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AssetCode::fromString($assetcode)
        );

        $this->computeTransformationLauncherSpy->assertAJobIsLaunchedWithAssetIdentifier($asset->getIdentifier());
    }

    /**
     * @Then the asset contains values computed by naming convention
     */
    public function theAssetContainsValuesComputedByNamingConvention()
    {
        $asset = $this->assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('PresentationView'),
            AssetCode::fromString('building')
        );

        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('title'),
            AssetFamilyIdentifier::fromString('PresentationView'),
        );
        $valueKey = ValueKey::create($attribute->getIdentifier(), ChannelReference::noReference(), LocaleReference::noReference());
        $value = $asset->findValue($valueKey);
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('house', $value->getData()->normalize());

        $attribute = $this->attributeRepository->getByCodeAndAssetFamilyIdentifier(
            AttributeCode::fromString('length'),
            AssetFamilyIdentifier::fromString('PresentationView'),
        );
        $valueKey = ValueKey::create($attribute->getIdentifier(), ChannelReference::noReference(), LocaleReference::noReference());
        $value = $asset->findValue($valueKey);
        Assert::assertInstanceOf(Value::class, $value);
        Assert::assertEquals('12', $value->getData()->normalize());
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

    /**
     * @When the connector collects an asset for an asset family with the wrong case
     */
    public function theConnectorCollectsAnAssetForAnAssetFamilyWithTheWrongCase()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'not_found_entity_asset_family_wrong_case.json'
        );
    }

    /**
     * @Then the PIM notifies the connector that the asset family does not exist
     */
    public function thePimNotifiesTheConnectorThatTheAssetFamilyDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'not_found_entity_asset_family_wrong_case.json'
        );
    }
}
