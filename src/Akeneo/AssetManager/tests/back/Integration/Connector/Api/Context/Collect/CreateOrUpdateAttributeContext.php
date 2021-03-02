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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributeByIdentifierAndCode;
use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType as MediaFileMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /** @var null|string */
    private $requestContract;

    /** @var null|Response */
    private $pimResponse;

    /** @var InMemoryFindConnectorAttributeByIdentifierAndCode */
    private $findConnectorAttribute;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AttributeRepositoryInterface $attributeRepository,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales,
        InMemoryFindConnectorAttributeByIdentifierAndCode $findConnectorAttribute
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->attributeRepository = $attributeRepository;
        $this->activatedLocales = $activatedLocales;
        $this->findConnectorAttribute = $findConnectorAttribute;
    }

    /**
     * @Given /^the ([a-zA-Z]+) asset family existing both in the ERP and in the PIM$/
     */
    public function theColorAssetFamilyExistingBothInTheErpAndInThePim(string $assetFamilyIdentifier)
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString(strtolower($assetFamilyIdentifier)),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @Given the Main Color attribute that is only part of the structure of the Color asset family in the ERP but not in the PIM
     */
    public function theMainColorAttributeThatIsOnlyPartOfTheStructureOfTheColorAssetFamilyInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_main_color_asset_family_attribute_creation.json';
    }

    /**
     * @Given /^the media file attribute Portrait that is only part of the structure of the Designer asset family in the ERP but not in the PIM$/
     */
    public function thePortraitAttributeThatIsOnlyPartOfTheStructureOfTheDesignerAssetFamilyInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_portrait_asset_family_attribute_creation.json';
    }


    /**
     * @When /^the connector collects this attribute from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheMainColorAttributeOfTheColorAssetFamilyFromTheERPToSynchronizeItWithThePIM()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the Main Color attribute is added to the structure of the Color asset family in the PIM with the properties coming from the ERP
     */
    public function theMainColorAttributeIsAddedToTheStructureOfTheColorAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_main_color_asset_family_attribute_creation.json'
        );

        $assetFamilyIdentifier = 'color';

        $identifier = AttributeIdentifier::create(
            (string) 'color',
            (string) 'main_color',
            md5('color_main_color')
        );

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Then /^the Portrait attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP$/
     */
    public function thePortraitAttributeIsAddedToTheStructureOfTheDesignerAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_portrait_asset_family_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'portrait', md5('designer_portrait'));

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = MediaFileAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the asset attribute Country that is only part of the structure of the Designer asset family in the ERP but not in the PIM$/
     */
    public function theAssetAttributeCountryThatIsOnlyPartOfTheStructureOfTheDesignerAssetFamilyInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_country_asset_family_attribute_creation.json';

        $country = AssetFamily::create(
            AssetFamilyIdentifier::fromString('country'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($country);
    }

    /**
     * @Given /^the option attribute Birth Date that is only part of the structure of the Designer asset family in the ERP but not in the PIM$/
     */
    public function theOptionAttributeBirthDateThatIsOnlyPartOfTheStructureOfTheDesignerAssetFamilyInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_birthdate_asset_family_attribute_creation.json';
    }

    /**
     * @Then /^the Birth Date attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP$/
     */
    public function theBirthDateAttributeIsAddedToTheStructureOfTheDesignerAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_birthdate_asset_family_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'birthdate', md5('designer_birthdate'));
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = OptionAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the mediaLink attribute Preview that is only part of the structure of the Designer asset family in the ERP but not in the PIM$/
     */
    public function theMediaLinkAttributePreviewThatIsOnlyPartOfTheStructureOfTheDesignerAssetFamilyInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_preview_asset_family_attribute_creation.json';
    }

    /**
     * @Then /^the Preview attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP$/
     */
    public function thePreviewAttributeIsAddedToTheStructureOfTheDesignerAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_preview_asset_family_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'preview', md5('designer_preview'));
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = MediaLinkAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString('image')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given the Main Color attribute that is both part of the structure of the Color asset family in the ERP and in the PIM but with some unsynchronized properties
     */
    public function theMainColorAttributeThatIsBothPartOfTheStructureOfTheColorAssetFamilyInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::fromString('main_color_identifier'),
            AssetFamilyIdentifier::fromString('color'),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $this->attributeRepository->create($attribute);

        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color']),
            'text',
            AttributeValuePerLocale::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'max_length' => 155,
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => AttributeValidationRule::REGULAR_EXPRESSION,
                'regular_expression' => '/\w+/',
            ]
        );
        $this->findConnectorAttribute->save($attribute->getAssetFamilyIdentifier(), $attribute->getCode(), $connectorAttribute);

        $this->requestContract = 'successful_main_color_asset_family_attribute_update.json';
    }

    /**
     * @Then the properties of the Main Color attribute are updated in the PIM with the properties coming from the ERP
     */
    public function thePropertiesOfTheMainColorAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_main_color_asset_family_attribute_update.json'
        );

        $assetFamilyIdentifier = 'color';

        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString('main_color_identifier')
        );
        $expectedAttribute = TextAttribute::createText(
            AttributeIdentifier::fromString('main_color_identifier'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString(AttributeValidationRule::NONE),
            AttributeRegularExpression::createEmpty()
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given the Portrait attribute that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties
     */
    public function thePortraitAttributeThatIsBothPartOfTheStructureOfTheDesignerAssetFamilyInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['gif']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );
        $this->attributeRepository->create($attribute);

        $connectorAttribute = new ConnectorAttribute(
            $attribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            'media_file',
            AttributeValuePerLocale::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'max_file_size' => 200.10,
                'allowed_extensions' => ['gif'],
            ]
        );
        $this->findConnectorAttribute->save($attribute->getAssetFamilyIdentifier(), $attribute->getCode(), $connectorAttribute);

        $this->requestContract = 'successful_portrait_asset_family_attribute_update.json';
    }

    /**
     * @Then the properties of the Portrait attribute are updated in the PIM with the properties coming from the ERP
     */
    public function thePropertiesOfThePortraitAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_portrait_asset_family_attribute_update.json'
        );

        $assetFamilyIdentifier = 'designer';
        $attributeIdentifier = AttributeIdentifier::create('designer', 'image', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = MediaFileAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['gif', 'png']),
            MediaFileMediaType::fromString(MediaFileMediaType::IMAGE)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the option attribute Birth Date that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theOptionAttributeBirthDateThatIsBothPartOfTheStructureOfTheDesignerAssetFamilyInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'birthdate', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        $this->attributeRepository->create($attribute);

        $connectorAttribute = new ConnectorAttribute(
            $attribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Birth date']),
            'option',
            AttributeValuePerLocale::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            []
        );
        $this->findConnectorAttribute->save($attribute->getAssetFamilyIdentifier(), $attribute->getCode(), $connectorAttribute);


        $this->requestContract = 'successful_birthdate_asset_family_attribute_update.json';
    }

    /**
     * @Then /^the properties of the Birth Date attribute are updated in the PIM with the properties coming from the ERP$/
     */
    public function thePropertiesOfTheBirthDateAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_birthdate_asset_family_attribute_update.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'birthdate', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = OptionAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date', 'fr_FR' => 'Date de naissance']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the media_link attribute Preview that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theMediaLinkAttributePreviewThatIsBothPartOfTheStructureOfTheDesignerAssetFamilyInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = MediaLinkAttribute::create(
            AttributeIdentifier::create('designer', 'preview', 'fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString('image')
        );
        $this->attributeRepository->create($attribute);

        $connectorAttribute = new ConnectorAttribute(
            $attribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Preview']),
            'media_link',
            AttributeValuePerLocale::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            ['media_type' => 'image']
        );
        $this->findConnectorAttribute->save($attribute->getAssetFamilyIdentifier(), $attribute->getCode(), $connectorAttribute);


        $this->requestContract = 'successful_preview_asset_family_attribute_update.json';
    }

    /**
     * @Then /^the properties of the Preview attribute are updated in the PIM with the properties coming from the ERP$/
     */
    public function thePropertiesOfThePreviewAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_preview_asset_family_attribute_update.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'preview', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = MediaLinkAttribute::create(
            $attributeIdentifier,
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('preview'),
            LabelCollection::fromArray(['en_US' => 'Preview', 'fr_FR' => 'Aperçu']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaLinkMediaType::fromString('image')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @When /^the connector collects the new Main Color attribute whose data does not comply with the business rules$/
     */
    public function theConnectorCollectsTheMainColorAttributeWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->requestContract = 'unprocessable_creation_main_color_asset_family_attribute_for_invalid_data.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute has data that does not comply with the business rules$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the existing Main Color attribute whose data does not comply with the business rules$/
     */
    public function theConnectorCollectsTheExistingMainColorAttributeWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->requestContract = 'unprocessable_update_main_color_asset_family_attribute_for_invalid_data.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the new Main color attribute with an invalid format$/
     */
    public function theConnectorCollectsTheNewMainColorAttributeWithAnInvalidFormat()
    {
        $this->requestContract = 'unprocessable_creation_main_color_asset_family_attribute_for_invalid_format.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute has an invalid format$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the existing Main color attribute with an invalid format$/
     */
    public function theConnectorCollectsTheExistingMainColorAttributeWithAnInvalidFormat()
    {
        $this->requestContract = 'unprocessable_update_main_color_asset_family_attribute_for_invalid_format.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }
}
