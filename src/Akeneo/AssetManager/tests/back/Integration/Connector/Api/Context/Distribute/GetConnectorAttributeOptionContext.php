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

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributeOption;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class GetConnectorAttributeOptionContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributeOption */
    private $findConnectorAttributeOption;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ConnectorReferenceEntity */
    private $referenceEntity;

    /** @var OptionAttribute */
    private $singleOptionAttribute;

    /** @var OptionCollectionAttribute */
    private $multiOptionAttribute;

    /** @var null|Response **/
    private $optionResponse;

    /** @var null|Response **/
    private $multiOptionResponse;

    /** @var null|Response **/
    private $nonExistentAttributeOptionResponse;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributeOption $findConnectorAttributeOption,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttributeOption = $findConnectorAttributeOption;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand reference entity$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        $this->referenceEntity = $this->createBrandReferenceEntity();
        $this->singleOptionAttribute = $this->createSingleOptionAttribute('nationality');
    }

    /**
     * @Given /^the French option that is one of the options of the Nationality attribute$/
     */
    public function theFrenchOptionThatIsOneOfTheOptionsOfTheNationalityAttribute()
    {
        $this->createOptionForSingleOptionAttribute();
    }

    /**
     * @When /^the connector requests the French option of the Nationality attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsTheFrenchOptionOfTheNationalityAttributeForTheBrandReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_french_nationality_option_for_brand_reference_entity.json"
        );
    }

    /**
     * @Then /^the PIM returns the French option$/
     */
    public function thePIMReturnsTheFrenchOption()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_french_nationality_option_for_brand_reference_entity.json"
        );
    }

    /**
     * @Given /^the Sales Area multiple options attribute that is part of the structure of the Brand reference entity$/
     */
    public function theSalesAreaMultipleOptionsAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntity()
    {
        $this->referenceEntity = $this->createBrandReferenceEntity();
        $this->multiOptionAttribute = $this->createMultiOptionAttribute('sales_area');
    }

    /**
     * @Given /^the Asia option that is one of the options of the Sales Area attribute$/
     */
    public function theAsiaOptionThatIsOneOfTheOptionsOfTheSalesAreaAttribute()
    {
        $this->createOptionForMultiOptionAttribute();
    }

    /**
     * @When /^the connector requests the Asia option of the Sales Area attribute for the Brand reference entity$/
     */
    public function theConnectorRequestsTheAsiaOptionOfTheSalesAreaAttributeForTheBrandReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->multiOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_asia_sales_area_option_for_brand_reference_entity.json"
        );
    }

    /**
     * @Then /^the PIM returns the Asia option$/
     */
    public function thePIMReturnsTheAsiaOption()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->multiOptionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_asia_sales_area_option_for_brand_reference_entity.json"
        );
    }

    /**
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand reference entity but has no options yet$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandReferenceEntityButHasNoOptionsYet()
    {
        $this->referenceEntity = $this->createBrandReferenceEntity();
        $this->singleOptionAttribute = $this->createSingleOptionAttribute('nationality');
    }

    /**
     * @When /^the connector requests a non\-existent option for a given attribute for a given reference entity$/
     */
    public function theConnectorRequestsANonExistentOptionForAGivenAttributeForAGivenReferenceEntity()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->nonExistentAttributeOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."non_existent_option_for_reference_entity_attribute.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the option is non existent for the Nationality attribute and the Brand reference entity$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheOptionIsNonExistentForTheNationalityAttributeAndTheBrandReferenceEntity()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->nonExistentAttributeOptionResponse,
            self::REQUEST_CONTRACT_DIR . "non_existent_option_for_reference_entity_attribute.json"
        );
    }

    private function createBrandReferenceEntity()
    {
        $identifier = ReferenceEntityIdentifier::fromString('brand_2');

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

    private function createSingleOptionAttribute(string $code)
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_2');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_1');

        $optionAttribute = OptionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString($code),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($optionAttribute);

        return $optionAttribute;
    }

    private function createOptionForSingleOptionAttribute()
    {
        $this->singleOptionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('french'),
                LabelCollection::fromArray(['fr_FR' => 'Francais'])
            ),
        ]);

        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('nationality'),
            LabelCollection::fromArray([ 'fr_FR' => 'Nationalite', 'en_US' => 'Nationality']),
            'option',
            AttributeValuePerLocale::fromBoolean($this->singleOptionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($this->singleOptionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $this->singleOptionAttribute->getAttributeOptions()
                ),
            ]
        );

        $this->findConnectorAttributeOption->save(
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_2'),
            AttributeCode::fromString('nationality'),
            $connectorAttribute
        );
    }

    private function createMultiOptionAttribute(string $code)
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_2');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_2');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString($code),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales area']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $this->attributeRepository->create($optionAttribute);

        return $optionAttribute;
    }

    private function createOptionForMultiOptionAttribute()
    {
        $this->multiOptionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('asia'),
                LabelCollection::fromArray(['fr_FR' => 'Asia'])
            ),
        ]);

        $connectorAttribute = new ConnectorAttribute(
            AttributeCode::fromString('sales_area'),
            LabelCollection::fromArray(['fr_FR' => 'Asia']),
            'option',
            AttributeValuePerLocale::fromBoolean($this->multiOptionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($this->multiOptionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                'options' => array_map(
                    function (AttributeOption $attributeOption) {
                        return $attributeOption->normalize();
                    },
                    $this->multiOptionAttribute->getAttributeOptions()
                ),
            ]
        );

        $this->findConnectorAttributeOption->save(
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_2'),
            AttributeCode::fromString('sales_area'),
            $connectorAttribute
        );
    }
}
