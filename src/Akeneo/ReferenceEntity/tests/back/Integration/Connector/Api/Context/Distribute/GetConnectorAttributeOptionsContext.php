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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Distribute;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributeOptions;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeOptionsContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributeOptions */
    private $findConnectorAttributeOptions;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var null|Response **/
    private $optionsResponse;

    /** @var null|Response **/
    private $multiOptionResponse;

    /** @var null|Response **/
    private $nonExistentAttributeResponse;

    /** @var null|Response */
    private $optionsNotSupportedResponse;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeOptions $findConnectorAttributeOptions,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttributeOptions = $findConnectorAttributeOptions;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the 4 options of the Nationality single option attribute$/
     */
    public function theOptionsOfTheNationalitySingleOptionAttribute()
    {
        $this->createBrandReferenceEntity();
        $this->createNationalityAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Nationality attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheNationalityAttributeForTheBrandReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionsResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_nationality_options_for_brand_reference_entity.json"
        );
    }

    /**
     * @Then /^the PIM returns the 4 options of the Nationality attribute for the Brand reference entity$/
     */
    public function thePIMReturnsTheOptionsOfTheNationalityAttributeForTheBrandReferenceEntity()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionsResponse,
            self::REQUEST_CONTRACT_DIR . "successful_nationality_options_for_brand_reference_entity.json"
        );
    }

    /**
     * @Given /^the 4 options of the Sales Area multiple options attribute$/
     */
    public function theOptionsOfTheSalesAreaMultipleOptionsAttribute()
    {
        $this->createBrandReferenceEntity();
        $this->createSalesAreaAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Sales Area attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheSalesAreaAttributeForTheBrandReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->multiOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_sales_area_options_for_brand_reference_entity.json"
        );
    }

    /**
     * @Then /^the PIM returns the (\d+) options of the Sales Area attribute for the Brand Reference entity$/
     */
    public function thePIMReturnsTheOptionsOfTheSalesAreaAttributeForTheBrandReferenceEntity($arg1)
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->multiOptionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_sales_area_options_for_brand_reference_entity.json"
        );
    }

    /**
     * @Given /^the Brand reference entity with no attribute in its structure$/
     */
    public function theBrandReferenceEntityWithNoAttributeInItsStructure()
    {
        $this->createBrandReferenceEntity();
    }

    /**
     * @When /^the connector requests the options of an attribute that is not part of the structure of the given reference entity$/
     */
    public function theConnectorRequestsTheOptionsOfAnAttributeThatIsNotPartOfTheStructureOfTheGivenReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->nonExistentAttributeResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."not_found_attribute_for_reference_entity_attribute_option.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute is not part of the structure of the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeIsNotPartOfTheStructureOfTheBrandReferenceEntity()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->nonExistentAttributeResponse,
            self::REQUEST_CONTRACT_DIR . "not_found_attribute_for_reference_entity_attribute_option.json"
        );
    }

    /**
     * @Given /^the Label text attribute that is part of the structure of the Brand reference entity$/
     */
    public function theLabelTextAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        $this->createBrandReferenceEntity();
        $this->createLabelAttribute();
    }

    /**
     * @When /^the connector requests all the options of the Label attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsAllTheOptionsOfTheLabelAttributeForTheBrandReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionsNotSupportedResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."options_not_supported_for_reference_entity_attribute.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not support options$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotSupportOptions()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionsNotSupportedResponse,
            self::REQUEST_CONTRACT_DIR . "options_not_supported_for_reference_entity_attribute.json"
        );
    }

    private function createBrandReferenceEntity()
    {
        $identifier = ReferenceEntityIdentifier::fromString('brand_3');

        $referenceEntity = ReferenceEntity::create(
            $identifier,
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand'
            ],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }

    private function createLabelAttribute()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('label_identifier');

        $textAttribute = TextAttribute::createText(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('another_label'),
            LabelCollection::fromArray(['en_US' => 'Label']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
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
            [
                'max_length' => $textAttribute->getMaxLength()->intValue(),
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => null,
                'regular_expression' => null
            ]
        );

        $this->findConnectorAttributeOptions->save(
            $referenceEntityIdentifier,
            $textAttribute->getCode(),
            $connectorAttribute
        );

        return $connectorAttribute;
    }

    private function createNationalityAttribute()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_2');

        $optionAttribute = OptionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('nationality'),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
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
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $optionAttribute->getAttributeOptions()
                )
            ]
        );

        $this->findConnectorAttributeOptions->save(
            ReferenceEntityIdentifier::fromString('brand_3'),
            AttributeCode::fromString('nationality'),
            $connectorAttribute
        );

        return $connectorAttribute;
    }

    private function createSalesAreaAttribute()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_3');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_3');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('sales_area'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales area']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
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
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $optionAttribute->getAttributeOptions()
                )
            ]
        );

        $this->findConnectorAttributeOptions->save(
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_3'),
            AttributeCode::fromString('sales_area'),
            $connectorAttribute
        );

        return $connectorAttribute;
    }
}
