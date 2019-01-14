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

namespace Akeneo\ReferenceEntity\Integration\Connector\Collection;

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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateOrUpdateAttributeOptionContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var null|string */
    private $requestContract;

    /** @var null|Response */
    private $pimResponse;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var OptionCollectionAttribute */
    private $optionAttribute;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given /^the Brand reference entity reference entity existing both in the ERP and in the PIM$/
     */
    public function theBrandReferenceEntityReferenceEntityExistingBothInTheERPAndInThePIM()
    {
        $this->createBrandReferenceEntity();
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
     * @When /^the connector collects the USA attribute option of the Sales area Attribute of the Brand reference entity from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheUSAAttributeOptionOfTheSalesAreaAttributeOfTheBrandReferenceEntityFromTheERPToSynchronizeItWithThePIM()
    {
        $client = $this->clientFactory->logIn('julia');
        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the USA attribute option of the Sales area attribute is added to the structure of the Brand reference entity in the PIM with the properties coming from the ERP$/
     */
    public function theUSAAttributeOptionOfTheSalesAreaAttributeIsAddedToTheStructureOfTheBrandReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
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
     * @Given /^some attributes that structure the Brand reference entity$/
     */
    public function someAttributesThatStructureTheBrandReferenceEntity()
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
     * @Given /^the Color attribute that structures the Brand reference entity and whose type is text$/
     */
    public function theColorAttributeThatStructuresTheBrandReferenceEntityAndWhoseTypeIsText()
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

    private function createBrandReferenceEntity()
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand_4'),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function createSalesAreaAttribute()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand_4');
        $attributeIdentifier = AttributeIdentifier::fromString('attribute_4');

        $this->optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('sales_area'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales area']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $attributeIdentifier = AttributeIdentifier::fromString('sales_identifier');

        $optionAttribute = OptionCollectionAttribute::create(
            $attributeIdentifier,
            $referenceEntityIdentifier,
            AttributeCode::fromString('sales_identifier'),
            LabelCollection::fromArray([ 'fr_FR' => 'Ventes', 'en_US' => 'Sales']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true)
        );

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
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
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'color', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('color'),
            LabelCollection::fromArray(['en_US' => 'Color']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($textAttribute);
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
     * @Given /^the Australia attribute option of the Sales area attribute of the Brand reference entity in the ERP and in the PIM but with some unsynchronized properties$/
     */
    public function theAustraliaAttributeOptionThatIsBothPartOfTheStructureOfTheBrandReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $this->createAustraliaAttributeOption();
    }

    /**
     * @When /^the connector collects the Australia attribute option of the Sales area Attribute of the Brand reference entity from the ERP to synchronize it with the PIM$/
     */
    public function theConnectorCollectsTheAustraliaAttributeOptionOfTheSalesAreaAttributeOfTheBrandReferenceEntityFromTheERPToSynchronizeItWithThePIM()
    {
        $this->requestContract = 'successful_australia_attribute_option_update.json';
        $client = $this->clientFactory->logIn('julia');

        $this->pimResponse = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR . $this->requestContract
        );
    }

    /**
     * @Then /^the Australia attribute option of the Sales area attribute is added to the structure of the Brand reference entity in the PIM with the properties coming from the ERP$/
     */
    public function theAustraliaAttributeOptionOfTheSalesAreaAttributeIsAddedToTheStructureOfTheBrandReferenceEntityInThePIMWithThePropertiesComingFromTheERP()
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
}
