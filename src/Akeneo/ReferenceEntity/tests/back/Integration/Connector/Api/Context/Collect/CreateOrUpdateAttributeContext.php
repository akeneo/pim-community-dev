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

namespace Akeneo\ReferenceEntity\Integration\Connector\Api\Context\Collect;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Common\Helper\OauthAuthenticatedClientFactory;
use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

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

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AttributeRepositoryInterface $attributeRepository,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->attributeRepository = $attributeRepository;
        $this->activatedLocales = $activatedLocales;
    }

    /**
     * @Given /^the ([a-zA-Z]+) reference entity existing both in the ERP and in the PIM$/
     */
    public function theColorReferenceEntityExistingBothInTheErpAndInThePim(string $referenceEntityIdentifier)
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString(strtolower($referenceEntityIdentifier)),
            [],
            Image::createEmpty()
        );

        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @Given the Main Color attribute that is only part of the structure of the Color reference entity in the ERP but not in the PIM
     */
    public function theMainColorAttributeThatIsOnlyPartOfTheStructureOfTheColorReferenceEntityInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_main_color_reference_entity_attribute_creation.json';
    }

    /**
     * @Given /^the image attribute Portrait that is only part of the structure of the Designer reference entity in the ERP but not in the PIM$/
     */
    public function thePortraitAttributeThatIsOnlyPartOfTheStructureOfTheDesignerReferenceEntityInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_portrait_reference_entity_attribute_creation.json';
    }


    /**
     * @When /^the connector collects this attribute from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheMainColorAttributeOfTheColorReferenceEntityFromTheERPToSynchronizeItWithThePIM()
    {
        Assert::assertNotNull($this->requestContract, 'The request contract must be defined first.');

        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then the Main Color attribute is added to the structure of the Color reference entity in the PIM with the properties coming from the ERP
     */
    public function theMainColorAttributeIsAddedToTheStructureOfTheColorReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_main_color_reference_entity_attribute_creation.json'
        );

        $referenceEntityIdentifier = 'color';

        $identifier = AttributeIdentifier::create(
            (string) 'color',
            (string) 'main_color',
            md5('color_main_color')
        );

        $attribute = $this->attributeRepository->getByIdentifier($identifier);
        $expectedAttribute = TextAttribute::createText(
            $identifier,
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Then /^the Portrait attribute is added to the structure of the Designer reference entity in the PIM with the properties coming from the ERP$/
     */
    public function thePortraitAttributeIsAddedToTheStructureOfTheDesignerReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_portrait_reference_entity_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'portrait', md5('designer_portrait'));

        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = ImageAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['png'])
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the record attribute Country that is only part of the structure of the Designer reference entity in the ERP but not in the PIM$/
     */
    public function theRecordAttributeCountryThatIsOnlyPartOfTheStructureOfTheDesignerReferenceEntityInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_country_reference_entity_attribute_creation.json';

        $country = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('country'),
            [],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($country);
    }

    /**
     * @Then /^the Country attribute is added to the structure of the Designer reference entity in the PIM with the properties coming from the ERP$/
     */
    public function theCountryAttributeIsAddedToTheStructureOfTheDesignerReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_country_reference_entity_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'country', md5('designer_country'));
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = RecordAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['fr_FR' => 'Pays', 'en_US' => 'Country']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the option attribute Birth Date that is only part of the structure of the Designer reference entity in the ERP but not in the PIM$/
     */
    public function theOptionAttributeBirthDateThatIsOnlyPartOfTheStructureOfTheDesignerReferenceEntityInTheERPButNotInThePIM()
    {
        $this->requestContract = 'successful_birthdate_reference_entity_attribute_creation.json';
    }

    /**
     * @Then /^the Birth Date attribute is added to the structure of the Designer reference entity in the PIM with the properties coming from the ERP$/
     */
    public function theBirthDateAttributeIsAddedToTheStructureOfTheDesignerReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_birthdate_reference_entity_attribute_creation.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'birthdate', md5('designer_birthdate'));
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = OptionAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given the Main Color attribute that is both part of the structure of the Color reference entity in the ERP and in the PIM but with some unsynchronized properties
     */
    public function theMainColorAttributeThatIsBothPartOfTheStructureOfTheColorReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::fromString('main_color_identifier'),
            ReferenceEntityIdentifier::fromString('color'),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $this->attributeRepository->create($attribute);

        $this->requestContract = 'successful_main_color_reference_entity_attribute_update.json';
    }

    /**
     * @Then the properties of the Main Color attribute are updated in the PIM with the properties coming from the ERP
     */
    public function thePropertiesOfTheMainColorAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_main_color_reference_entity_attribute_update.json'
        );

        $referenceEntityIdentifier = 'color';

        $attribute = $this->attributeRepository->getByIdentifier(
            AttributeIdentifier::fromString('main_color_identifier')
        );
        $expectedAttribute = TextAttribute::createText(
            AttributeIdentifier::fromString('main_color_identifier'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString(AttributeValidationRule::NONE),
            AttributeRegularExpression::createEmpty()
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given the Portrait attribute that is both part of the structure of the Designer reference entity in the ERP and in the PIM but with some unsynchronized properties
     */
    public function thePortraitAttributeThatIsBothPartOfTheStructureOfTheDesignerReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('200.10'),
            AttributeAllowedExtensions::fromList(['gif'])
        );
        $this->attributeRepository->create($attribute);

        $this->requestContract = 'successful_portrait_reference_entity_attribute_update.json';
    }

    /**
     * @Then the properties of the Portrait attribute are updated in the PIM with the properties coming from the ERP
     */
    public function thePropertiesOfThePortraitAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_portrait_reference_entity_attribute_update.json'
        );

        $referenceEntityIdentifier = 'designer';
        $attributeIdentifier = AttributeIdentifier::create('designer', 'image', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = ImageAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('portrait'),
            LabelCollection::fromArray(['fr_FR' => 'Image autobiographique', 'en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(['gif', 'png'])
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the Country attribute that is both part of the structure of the Designer reference entity in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theCountryAttributeThatIsBothPartOfTheStructureOfTheDesignerReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = RecordAttribute::create(
            AttributeIdentifier::create('designer', 'country', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['en_US' => 'Country']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );
        $this->attributeRepository->create($attribute);

        $this->requestContract = 'successful_country_reference_entity_attribute_update.json';
    }

    /**
     * @Then /^the properties of the Country attribute are updated in the PIM with the properties coming from the ERP$/
     */
    public function thePropertiesOfTheCountryAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_country_reference_entity_attribute_update.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'country', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = RecordAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('country'),
            LabelCollection::fromArray(['fr_FR' => 'Pays', 'en_US' => 'Country']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString('country')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @Given /^the option attribute Birth Date that is both part of the structure of the Designer reference entity in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theOptionAttributeBirthDateThatIsBothPartOfTheStructureOfTheDesignerReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = OptionAttribute::create(
            AttributeIdentifier::create('designer', 'birthdate', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($attribute);

        $this->requestContract = 'successful_birthdate_reference_entity_attribute_update.json';
    }

    /**
     * @Then /^the properties of the Birth Date attribute are updated in the PIM with the properties coming from the ERP$/
     */
    public function thePropertiesOfTheBirthDateAttributeAreUpdatedInThePIMWithThePropertiesComingFromTheERP()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->pimResponse,
            self::REQUEST_CONTRACT_DIR . 'successful_birthdate_reference_entity_attribute_update.json'
        );

        $attributeIdentifier = AttributeIdentifier::create('designer', 'birthdate', 'fingerprint');
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);
        $expectedAttribute = OptionAttribute::create(
            $attributeIdentifier,
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('birthdate'),
            LabelCollection::fromArray(['en_US' => 'Birth date', 'fr_FR' => 'Date de naissance']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }

    /**
     * @When /^the connector collects the new Main Color attribute whose data does not comply with the business rules$/
     */
    public function theConnectorCollectsTheMainColorAttributeWhoseDataDoesNotComplyWithTheBusinessRules()
    {
        $this->requestContract = 'unprocessable_creation_main_color_reference_entity_attribute_for_invalid_data.json';
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
        $this->requestContract = 'unprocessable_update_main_color_reference_entity_attribute_for_invalid_data.json';
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
        $this->requestContract = 'unprocessable_creation_main_color_reference_entity_attribute_for_invalid_format.json';
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
        $this->requestContract = 'unprocessable_update_main_color_reference_entity_attribute_for_invalid_format.json';
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }
}
