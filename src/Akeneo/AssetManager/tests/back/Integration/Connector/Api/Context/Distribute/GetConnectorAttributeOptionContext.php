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

use Akeneo\AssetManager\Common\Fake\Connector\InMemoryFindConnectorAttributeOption;
use Akeneo\AssetManager\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\AssetManager\Common\Helper\WebClientHelper;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\Connector\ConnectorAttribute;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
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

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ConnectorAssetFamily */
    private $assetFamily;

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
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorAttributeOption = $findConnectorAttributeOption;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand asset family$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandAssetFamily()
    {
        $this->assetFamily = $this->createBrandAssetFamily();
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
     * @When /^the connector requests the French option of the Nationality attribute for the Brand asset family$/
     */
    public function theConnectorRequestsTheFrenchOptionOfTheNationalityAttributeForTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->optionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_french_nationality_option_for_brand_asset_family.json"
        );
    }

    /**
     * @Then /^the PIM returns the French option$/
     */
    public function thePIMReturnsTheFrenchOption()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->optionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_french_nationality_option_for_brand_asset_family.json"
        );
    }

    /**
     * @Given /^the Sales Area multiple options attribute that is part of the structure of the Brand asset family$/
     */
    public function theSalesAreaMultipleOptionsAttributeThatIsPartOfTheStructureOfTheBrandAssetFamily()
    {
        $this->assetFamily = $this->createBrandAssetFamily();
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
     * @When /^the connector requests the Asia option of the Sales Area attribute for the Brand asset family$/
     */
    public function theConnectorRequestsTheAsiaOptionOfTheSalesAreaAttributeForTheBrandAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->multiOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_asia_sales_area_option_for_brand_asset_family.json"
        );
    }

    /**
     * @Then /^the PIM returns the Asia option$/
     */
    public function thePIMReturnsTheAsiaOption()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->multiOptionResponse,
            self::REQUEST_CONTRACT_DIR . "successful_asia_sales_area_option_for_brand_asset_family.json"
        );
    }

    /**
     * @Given /^the Nationality single option attribute that is part of the structure of the Brand asset family but has no options yet$/
     */
    public function theNationalitySingleOptionAttributeThatIsPartOfTheStructureOfTheBrandAssetFamilyButHasNoOptionsYet()
    {
        $this->assetFamily = $this->createBrandAssetFamily();
        $this->singleOptionAttribute = $this->createSingleOptionAttribute('nationality');
    }

    /**
     * @When /^the connector requests a non\-existent option for a given attribute for a given asset family$/
     */
    public function theConnectorRequestsANonExistentOptionForAGivenAttributeForAGivenAssetFamily()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->nonExistentAttributeOptionResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."non_existent_option_for_asset_family_attribute.json"
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the option is non existent for the Nationality attribute and the Brand asset family$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheOptionIsNonExistentForTheNationalityAttributeAndTheBrandAssetFamily()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->nonExistentAttributeOptionResponse,
            self::REQUEST_CONTRACT_DIR . "non_existent_option_for_asset_family_attribute.json"
        );
    }

    private function createBrandAssetFamily()
    {
        $identifier = AssetFamilyIdentifier::fromString('brand_2');

        $assetFamily = AssetFamily::create(
            $identifier,
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand'
            ],
            Image::createEmpty()
        );

        $this->assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }

    private function createSingleOptionAttribute(string $code)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_2');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_1');

        $optionAttribute = OptionAttribute::create(
            $attributeIdentifier,
            $assetFamilyIdentifier,
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
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_2'),
            AttributeCode::fromString('nationality'),
            $connectorAttribute
        );
    }

    private function createMultiOptionAttribute(string $code)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_2');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_2');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $assetFamilyIdentifier,
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
            $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_2'),
            AttributeCode::fromString('sales_area'),
            $connectorAttribute
        );
    }
}
