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
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
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

    /** @var null|string */
    private $requestContract;

    /** @var null|Response */
    private $pimResponse;

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
     * @Given the Color reference entity existing both in the ERP and in the PIM
     */
    public function theColorReferenceEntityExistingBothInTheErpAndInThePim()
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('color'),
            [],
            Image::createEmpty()
        );

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
     * @When the connector collects the Main Color attribute of the Color reference entity from the ERP to synchronize it with the PIM
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
            AttributeOrder::fromInteger(0),
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
     * @Given the Main Color attribute that is both part of the structure of the Color reference entity in the ERP and in the PIM but with some unsynchronized properties
     */
    public function theMainColorAttributeThatIsBothPartOfTheStructureOfTheColorReferenceEntityInTheERPAndInThePIMButWithSomeUnsynchronizedProperties()
    {
        $attribute = TextAttribute::createText(
            AttributeIdentifier::fromString('main_color_identifier'),
            ReferenceEntityIdentifier::fromString('color'),
            AttributeCode::fromString('main_color'),
            LabelCollection::fromArray(['en_US' => 'Main color', 'fr_FR' => 'Couleur principale']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
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
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        Assert::assertEquals($expectedAttribute, $attribute);
    }
}
