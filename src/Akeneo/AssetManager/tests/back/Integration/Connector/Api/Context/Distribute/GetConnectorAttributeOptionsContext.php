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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributeOptions;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
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

class GetConnectorAttributeOptionsContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private InMemoryFindConnectorAttributeOptions $findConnectorAttributeOptions;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AttributeRepositoryInterface $attributeRepository;

    /** @var null|Response **/
    private ?Response $optionsResponse = null;

    /** @var null|Response **/
    private ?Response $multiOptionResponse = null;

    /** @var null|Response **/
    private ?Response $nonExistentAttributeResponse = null;

    private ?Response $optionsNotSupportedResponse = null;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeOptions $findConnectorAttributeOptions,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttributeOptions = $findConnectorAttributeOptions;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the 4 options of the Nationality single option attribute$/
     */
    public function theOptionsOfTheNationalitySingleOptionAttribute()
    {
        $this->createBrandAssetFamily();
        $this->createNationalityAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Nationality attribute for the Brand asset family$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheNationalityAttributeForTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionsResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_nationality_options_for_brand_asset_family.json"
        );
    }

    /**
     * @Then /^the PIM returns the 4 options of the Nationality attribute for the Brand asset family$/
     */
    public function thePIMReturnsTheOptionsOfTheNationalityAttributeForTheBrandAssetFamily()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionsResponse,
            self::REQUEST_CONTRACT_DIR . "successful_nationality_options_for_brand_asset_family.json"
        );
    }

    /**
     * @Given /^the 4 options of the Sales Area multiple options attribute$/
     */
    public function theOptionsOfTheSalesAreaMultipleOptionsAttribute()
    {
        $this->createBrandAssetFamily();
        $this->createSalesAreaAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Sales Area attribute for the Brand asset family$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheSalesAreaAttributeForTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->multiOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_sales_area_options_for_brand_asset_family.json"
        );
    }

    /**
     * @Then /^the PIM returns the (\d+) options of the Sales Area attribute for the Brand Asset family$/
     */
    public function thePIMReturnsTheOptionsOfTheSalesAreaAttributeForTheBrandAssetFamily($arg1)
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->multiOptionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_sales_area_options_for_brand_asset_family.json"
        );
    }

    /**
     * @Given /^the Brand asset family with no attribute in its structure$/
     */
    public function theBrandAssetFamilyWithNoAttributeInItsStructure()
    {
        $this->createBrandAssetFamily();
    }

    /**
     * @When /^the connector requests the options of an attribute that is not part of the structure of the given asset family$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeThatIsNotPartOfTheStructureOfTheGivenAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->nonExistentAttributeResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."not_found_attribute_for_asset_family_attribute_option.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute is not part of the structure of the Brand asset family$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeIsNotPartOfTheStructureOfTheBrandAssetFamily()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->nonExistentAttributeResponse,
            self::REQUEST_CONTRACT_DIR . "not_found_attribute_for_asset_family_attribute_option.json"
        );
    }

    /**
     * @Given /^the Label text attribute that is part of the structure of the Brand asset family$/
     */
    public function theLabelTextAttributeThatIsPartOfTheStructureOfTheBrandAssetFamily()
    {
        $this->createBrandAssetFamily();
        $this->createLabelAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Label attribute for the Brand asset family$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheLabelAttributeForTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionsNotSupportedResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."options_not_supported_for_asset_family_attribute.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not support options$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotSupportOptions()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionsNotSupportedResponse,
            self::REQUEST_CONTRACT_DIR . "options_not_supported_for_asset_family_attribute.json"
        );
    }

    private function createBrandAssetFamily()
    {
        $identifier = AssetFamilyIdentifier::fromString('brand_3');

        $assetFamily = AssetFamily::create(
            $identifier,
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand'
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }

    private function createLabelAttribute()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('label_identifier');

        $textAttribute = TextAttribute::createText(
            $attributeIdentifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('another_label'),
            LabelCollection::fromArray(['en_US' => 'Label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($textAttribute);

        $connectorAttribute = new ConnectorAttribute(
            $textAttribute->getCode(),
            LabelCollection::fromArray(['en_US' => 'Label', 'fr_FR' => 'Label']),
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

        $this->findConnectorAttributeOptions->save(
            $assetFamilyIdentifier,
            $textAttribute->getCode(),
            $connectorAttribute
        );

        return $connectorAttribute;
    }

    private function createNationalityAttribute()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_2');

        $optionAttribute = OptionAttribute::create(
            $attributeIdentifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('nationality'),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais'])
            ),
            AttributeOption::create(
                OptionCode::fromString('australian'),
                LabelCollection::fromArray(['en_US' => 'Australian'])
            ),
            AttributeOption::create(
                OptionCode::fromString('lebanese'),
                LabelCollection::fromArray(['ar_LB' => 'Lebanese'])
            ),
            AttributeOption::create(
                OptionCode::fromString('cat'),
                LabelCollection::fromArray(['cat_CAT' => 'Cat'])
            )
        ]);

        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('nationality'),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            'option',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'options' => array_map(
                    fn(AttributeOption $attributeOption) => $attributeOption->normalize(),
                    $optionAttribute->getAttributeOptions()
                )
            ]
        );

        $this->findConnectorAttributeOptions->save(
            AssetFamilyIdentifier::fromString('brand_3'),
            AttributeCode::fromString('nationality'),
            $connectorAttribute
        );

        return $connectorAttribute;
    }

    private function createSalesAreaAttribute()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_3');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('sales_area'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales area']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('china'),
                LabelCollection::fromArray(['en_US' => 'China'])
            ),
            AttributeOption::create(
                OptionCode::fromString('australia'),
                LabelCollection::fromArray(['en_US' => 'Australia'])
            ),
            AttributeOption::create(
                OptionCode::fromString('lebanon'),
                LabelCollection::fromArray(['en_US' => 'Lebanon'])
            ),
            AttributeOption::create(
                OptionCode::fromString('denmark'),
                LabelCollection::fromArray(['en_US' => 'Denmark'])
            )
        ]);

        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('sales_area'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales area']),
            'option',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            [
                'options' => array_map(
                    fn(AttributeOption $attributeOption) => $attributeOption->normalize(),
                    $optionAttribute->getAttributeOptions()
                )
            ]
        );

        $this->findConnectorAttributeOptions->save(
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_3'),
            AttributeCode::fromString('sales_area'),
            $connectorAttribute
        );

        return $connectorAttribute;
    }
}
