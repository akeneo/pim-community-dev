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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributesByAssetFamilyIdentifier;
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
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributesContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private InMemoryFindConnectorAttributesByAssetFamilyIdentifier $findConnectorAssetFamilyAttributes;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    private ?Response $attributesForAssetFamily = null;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributesByAssetFamilyIdentifier $findConnectorAssetFamilyAttributes,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAssetFamilyAttributes = $findConnectorAssetFamilyAttributes;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^6 attributes that structure the Brand asset family in the PIM$/
     */
    public function attributesThatStructureTheBrandAssetFamilyInThePIM()
    {
        $assetFamilyIdentifier = 'brand';

        $this->createTextAttribute($assetFamilyIdentifier);
        $this->createMediaFileAttribute($assetFamilyIdentifier);
        $this->createOptionAttribute($assetFamilyIdentifier);
        $this->createMultiOptionAttribute($assetFamilyIdentifier);
        $this->createSingleLinkAttribute($assetFamilyIdentifier);
        $this->createMultiLinkAttribute($assetFamilyIdentifier);

        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @When /^the connector requests the structure of the Brand asset family from the PIM$/
     */
    public function theConnectorRequestsTheStructureOfTheBrandAssetFamilyFromThePIM()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->attributesForAssetFamily = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_brand_asset_family_attributes.json"
        );
    }

    /**
     * @Then /^the PIM returns the 6 attributes of the Brand asset family$/
     */
    public function thePIMReturnsTheAttributesOfTheBrandAssetFamily()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->attributesForAssetFamily,
            self::REQUEST_CONTRACT_DIR . "successful_brand_asset_family_attributes.json"
        );
    }

    /**
     * @Given /^some asset families with some attributes$/
     */
    public function someAssetFamiliesWithSomeAttributes()
    {
        $firstIdentifier = 'whatever_1';

        $this->createTextAttribute($firstIdentifier);

        $firstAssetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($firstIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($firstAssetFamily);

        $secondIdentifier = 'whatever_2';

        $this->createMediaFileAttribute($secondIdentifier);

        $secondAssetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString($secondIdentifier),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($secondAssetFamily);
    }

    private function createTextAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'description';

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $this->attributeRepository->create($textAttribute);

        $textConnectorAttribute = new ConnectorAttribute(
            $textAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean($textAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($textAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'max_length' => $textAttribute->getMaxLength()->intValue(),
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => null,
                'regular_expression' => null
            ]
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $textConnectorAttribute
        );
    }

    private function createMediaFileAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'photo';

        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg']),
            MediaType::fromString(MediaType::IMAGE)
        );

        $this->attributeRepository->create($mediaFileAttribute);

        $mediaFileAttribute = new ConnectorAttribute(
            $mediaFileAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            'media_file',
            AttributeValuePerLocale::fromBoolean($mediaFileAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($mediaFileAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'allowed_extensions' => ['jpg'],
                'max_file_size' => '10',
                'media_type' => MediaType::IMAGE
            ]
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $mediaFileAttribute
        );
    }

    private function createOptionAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'nationality';

        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'Nationalité', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute = new ConnectorAttribute(
            $optionAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Nationality', 'fr_FR' => 'Nationalité']),
            'single_option',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            []
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $optionAttribute
        );
    }

    private function createMultiOptionAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'sales_areas';

        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'Zones de vente', 'en_US' => 'Sales areas']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute = new ConnectorAttribute(
            $optionAttribute->getCode(),
            LabelCollection::fromArray(['fr_FR' => 'Zones de vente', 'en_US' => 'Sales areas']),
            'multiple_options',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            []
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $optionAttribute
        );
    }

    private function createSingleLinkAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'country';

        $linkAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['en_US' => 'Country', 'fr_FR' => 'Pays']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($linkAttribute);

        $linkAttribute = new ConnectorAttribute(
            $linkAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Country', 'fr_FR' => 'Pays']),
            'asset_family_single_link',
            AttributeValuePerLocale::fromBoolean($linkAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($linkAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            [
                "asset_family_code" => 'country'
            ]
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $linkAttribute
        );
    }

    private function createMultiLinkAttribute(string $assetFamilyIdentifier)
    {
        $attributeIdentifier = 'designers';

        $multiLinkAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($assetFamilyIdentifier, $attributeIdentifier, 'test'),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['en_US' => 'Designers', 'fr_FR' => 'Designeurs']),
            AttributeOrder::fromInteger(7),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($multiLinkAttribute);

        $multiLinkAttribute = new ConnectorAttribute(
            $multiLinkAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Designers', 'fr_FR' => 'Designeurs']),
            'asset_family_multiple_links',
            AttributeValuePerLocale::fromBoolean($multiLinkAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($multiLinkAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                "asset_family_code" => 'designer'
            ]
        );

        $this->findConnectorAssetFamilyAttributes->save(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            $multiLinkAttribute
        );
    }
}
