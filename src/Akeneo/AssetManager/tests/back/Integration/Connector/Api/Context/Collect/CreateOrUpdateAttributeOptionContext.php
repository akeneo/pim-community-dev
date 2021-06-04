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

use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
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
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAttributeOptionContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    private OauthAuthenticatedClientFactory $clientFactory;

    private WebClientHelper $webClientHelper;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private ?string $requestContract = null;

    private ?Response $pimResponse = null;

    private AttributeRepositoryInterface $attributeRepository;

    private ?OptionCollectionAttribute $optionAttribute = null;

    private InMemoryFindActivatedLocalesByIdentifiers $activatedLocales;

    public function __construct(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AttributeRepositoryInterface $attributeRepository,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales
    ) {
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->attributeRepository = $attributeRepository;
        $this->activatedLocales = $activatedLocales;
    }

    /**
     * @Given /^the Brand asset family asset family existing both in the ERP and in the PIM$/
     */
    public function theBrandAssetFamilyAssetFamilyExistingBothInTheERPAndInThePIM()
    {
        $this->createBrandAssetFamily();
    }

    /**
     * @Given /^the Sales area attribute existing both in the ERP and in the PIM$/
     */
    public function theSalesAreaAttributeExistingBothInTheERPAndInThePIM()
    {
        $this->createSalesAreaAttribute();
    }

    /**
     * @Given /^the USA attribute option that only exists in the ERP but not in the PIM$/
     */
    public function theUSAAttributeOptionThatOnlyExistsInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_usa_attribute_option_creation.json';
    }

    /**
     * @When /^the connector collects the USA attribute option of the Sales area Attribute of the Brand asset family from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheUSAAttributeOptionOfTheSalesAreaAttributeOfTheBrandAssetFamilyFromTheERPToSynchronizeItWithThePIM()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the USA attribute option of the Sales area attribute is added to the structure of the Brand asset family in the PIM with the properties coming from the ERP$/
     */
    public function theUSAAttributeOptionOfTheSalesAreaAttributeIsAddedToTheStructureOfTheBrandAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_usa_attribute_option_creation.json'
        );

        $identifier = AttributeIdentifier::fromString('attribute_4');

        $attribute = $this->attributeRepository->getByIdentifier($identifier)->normalize();
        $expectedAttributeOptions = [
            [
                'code' => 'china',
                'labels' => [
                    'en_US' => 'China'
                ]
            ],
            [
                'code' => 'usa',
                'labels' => [
                    'en_US' => 'USA',
                    'fr_FR' => 'Aux Etats-Unis'
                ]
            ],
        ];

        Assert::assertEquals($expectedAttributeOptions, $attribute['options']);
    }

    /**
     * @Given /^some attributes that structure the Brand asset family$/
     */
    public function someAttributesThatStructureTheBrandAssetFamily()
    {
        $this->createSomeBrandAttributes();
    }

    /**
     * @When /^the connector collects an attribute option of a non\-existent attribute$/
     */
    public function theConnectorCollectsAnAttributeOptionOfANonExistentAttribute()
    {
        $this->requestContract = 'not_found_attribute_for_an_attribute_option.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does not exist$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesNotExist()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Given /^the Color attribute that structures the Brand asset family and whose type is text$/
     */
    public function theColorAttributeThatStructuresTheBrandAssetFamilyAndWhoseTypeIsText()
    {
        $this->createColorTextAttribute();
    }

    /**
     * @When /^the connector collects an attribute option of an attribute that does not accept options$/
     */
    public function theConnectorCollectsAnAttributeOptionOfAnAttributeThatDoesNotAcceptOptions()
    {
        $this->requestContract = 'attribute_does_not_support_options_for_an_attribute_option.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute does accept options$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeDoesAcceptOptions()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    private function createBrandAssetFamily()
    {
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand_4'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );

        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->assetFamilyRepository->create($assetFamily);
    }

    private function createSalesAreaAttribute()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand_4');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_4');

        $this->optionAttribute = OptionCollectionAttribute::create(
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

        $this->attributeRepository->create($this->optionAttribute);

        $this->optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('china'),
                LabelCollection::fromArray(['en_US' => 'China'])
            )
        ]);
    }

    private function createSomeBrandAttributes()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $attributeIdentifier = AttributeIdentifier::fromString('sales_identifier');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $assetFamilyIdentifier,
            AttributeCode::fromString('sales_identifier'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
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

        $this->attributeRepository->create($optionAttribute);
        $this->attributeRepository->create($textAttribute);
    }

    private function createColorTextAttribute()
    {
        $this->attributeRepository->create(TextAttribute::createText(
            AttributeIdentifier::create('brand', 'color', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray(['en_US' => 'Color']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        ));
    }

    private function createAustraliaAttributeOption()
    {
        $this->optionAttribute->setOptions([
            AttributeOption::create(
                OptionCode::fromString('australia'),
                LabelCollection::fromArray(['en_US' => 'Australia'])
            )
        ]);

        $this->attributeRepository->update($this->optionAttribute);
    }

    /**
     * @Given /^the Australia attribute option of the Sales area attribute of the Brand asset family in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theAustraliaAttributeOptionThatIsBothPartOfTheStructureOfTheBrandAssetFamilyInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $this->createAustraliaAttributeOption();
    }

    /**
     * @When /^the connector collects the Australia attribute option of the Sales area Attribute of the Brand asset family from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheAustraliaAttributeOptionOfTheSalesAreaAttributeOfTheBrandAssetFamilyFromTheERPToSynchronizeItWithThePIM()
    {
        $this->requestContract = 'successful_australia_attribute_option_update.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the Australia attribute option of the Sales area attribute is added to the structure of the Brand asset family in the PIM with the properties coming from the ERP$/
     */
    public function theAustraliaAttributeOptionOfTheSalesAreaAttributeIsAddedToTheStructureOfTheBrandAssetFamilyInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );

        $identifier = AttributeIdentifier::fromString('attribute_4');
        $attribute = $this->attributeRepository->getByIdentifier($identifier)->normalize();

        $expectedAttributeOptions = [
            [
                'code' => 'australia',
                'labels' => [
                    'fr_FR' => 'Australie',
                    'en_US' => 'Australia'
                ]
            ],
        ];

        Assert::assertEquals($expectedAttributeOptions, $attribute['options']);
    }

    /**
     * @When /^the connector collects the USA attribute option with an invalid format$/
     */
    public function theConnectorCollectsTheUSAAttributeOptionWithAnInvalidFormat()
    {
        $this->requestContract = 'unprocessable_creation_usa_attribute_option_for_invalid_format.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the attribute option has an invalid format$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheAttributeOptionHasAnInvalidFormat()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the Australia attribute option with an invalid format$/
     */
    public function theConnectorCollectsTheAustraliaAttributeOptionWithAnInvalidFormat()
    {
        $this->requestContract = 'unprocessable_update_australia_attribute_option_for_invalid_format.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the USA attribute option whose data does not comply with the business rules$/
     */
    public function theConnectorCollectsTheUSAAttributeOptionWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->requestContract = 'unprocessable_creation_usa_attribute_option_for_invalid_data.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the PIM notifies the connector about an error indicating that the option attribute has data that does not comply with the business rules$/
     */
    public function thePIMNotifiesTheConnectorAboutAnErrorIndicatingThatTheOptionAttributeHasDataThatDoesNotComplyWithTheBusinessRules()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @When /^the connector collects the Australia attribute option whose data does not comply with the business rules$/
     */
    public function theConnectorCollectsTheAustraliaAttributeOptionWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->requestContract = 'unprocessable_update_australia_attribute_option_for_invalid_data.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }
}
