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
use Akeneo\AssetManager\Common\Fake\InMemoryClock;
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
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
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

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ?Response $pimResponse = null;

    private ?string $requestContract = null;

    private InMemoryChannelExists $channelExists;

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    private InMemoryFindActivatedLocalesPerChannels $activatedLocalesPerChannels;

    private InMemoryGetAttributeIdentifier $getAttributeIdentifier;

    private InMemoryGetAssetCollectionTypeAdapter $findAssetCollectionTypeACL;

    private AttributeRepositoryInterface $attributeRepository;

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
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
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
        InMemoryClock::$actualDateTime = new \DateTime('1990-01-01');
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
        $this->loadTextAttribute('brand', 'title', 'Title', 3);

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
            AttributeCode::fromString(AssetFamily::DEFAULT_ATTRIBUTE_AS_MAIN_MEDIA_CODE)
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
                TransformationLabel::fromString('thumbnail_100x80'),
                Source::createFromNormalized(['attribute' => 'main_image', 'channel'=> null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'thumbnail', 'channel'=> null, 'locale' => null]),
                OperationCollection::create([
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                ]),
                '1_',
                '_2',
                InMemoryClock::$actualDateTime
            ),
        ]));
        $expectedBrand->updateNamingConvention(NamingConvention::createFromNormalized([
            'source' => ['property' => 'media', 'locale' => null, 'channel' => null],
            'pattern' => '/the_pattern/',
            'abort_asset_creation_on_error' => true,
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
     * @Given some asset families with media file attributes
     */
    public function someAssetFamiliesWithMediaFileAttribute()
    {
        $this->channelExists->save(ChannelIdentifier::fromCode('ecommerce'));
        $this->channelExists->save(ChannelIdentifier::fromCode('print'));
        $this->channelExists->save(ChannelIdentifier::fromCode('other'));
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
        $this->loadMediaFileAttribute('brand', 'main_image', 'Main image', 2);
        $this->loadMediaFileAttribute('brand', 'thumbnail', 'Thumbnail image', 3);
        $this->attributeRepository->create(MediaFileAttribute::create(
            AttributeIdentifier::create('brand', 'test_scopable_localizable', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('test_scopable_localizable'),
            LabelCollection::fromArray(['en_US' => 'label']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
        ));
        $this->loadTextAttribute('brand', 'title', 'Title', 5);
        $this->loadTextAttribute('brand', 'sub_title', 'Sub Title', 6);
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
     * @When the connector collects an asset family with a code that already exists with wrong case
     */
    public function collectAnAssetFamilyWithACodeThatAlreadyExistsWithWrongCase()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_with_bad_case_code.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about an error indicating that the asset family has a code that already exist with wrong case
     */
    public function thePimNotifiesTheConnectorAboutAnErrorIndicatingThatTheAssetFamilyHasACodeThatAlreadyExistWithWrongCase()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_with_bad_case_code.json'
        );
    }

    /**
     * @When the connector collects an asset family whose data does not comply with the business rules
     */
    public function theConnectorCollectsAnAssetFamilyWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_for_invalid_data.json'
        );
    }

    /**
     * @When the connector collects an asset family whose transformations do not comply with the business rules
     */
    public function theConnectorCollectsAnAssetFamilyWhoseTransformationsDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_transformation_for_invalid_data.json'
        );
    }

    /**
     * @When the connector collects an asset family whose transformation have invalid operation parameters
     */
    public function theConnectorCollectsAnAssetFamilyWhoseTransformationHaveInvalidOperationParameters()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_transformation_for_invalid_operation_parameters.json'
        );
    }

    /**
     * @When the connector collects an asset family whose naming convention do not comply with the business rules
     */
    public function theConnectorCollectsAnAssetFamilyWhoseNamingConventionDoNotComplyWithTheBusinessRules()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_naming_convention_for_invalid_data.json'
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
     * @Then the PIM notifies the connector about errors indicating that the asset family has transformations that do not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutErrorsIndicatingThatTheAssetFamilyHasTransformationsThatDoNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_transformation_for_invalid_data.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about errors indicating that the asset family has a transformation that does not have valid operation parameters
     */
    public function thePimNotifiesTheConnectorAboutErrorsIndicatingThatTheAssetFamilyHasATransformationThatDoesNotHaveValidOperationParameters()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_transformation_for_invalid_operation_parameters.json'
        );
    }

    /**
     * @Then the PIM notifies the connector about errors indicating that the asset family naming convention that do not comply with the business rules
     */
    public function thePimNotifiesTheConnectorAboutErrorsIndicatingThatTheAssetFamilyNamingConventionThatDoNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'unprocessable_brand_asset_family_naming_convention_for_invalid_data.json'
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
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(self::VALID_EXTENSIONS),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($name);
    }

    private function loadTextAttribute(string $assetFamilyIdentifier, string $code, string $label, int $order): void
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create($assetFamilyIdentifier, $code, 'fingerprint'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($code),
            LabelCollection::fromArray(['en_US' => $label]),
            AttributeOrder::fromInteger($order),
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
}
