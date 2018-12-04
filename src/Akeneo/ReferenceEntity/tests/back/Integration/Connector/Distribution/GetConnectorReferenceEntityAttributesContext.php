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

namespace Akeneo\ReferenceEntity\Integration\Connector\Distribution;

use Akeneo\ReferenceEntity\Common\Fake\Connector\InMemoryFindConnectorAttributesByReferenceEntityIdentifier;
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

class GetConnectorReferenceEntityAttributesContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Distribute/';

    /** @var OauthAuthenticatedClientFactory */
    private $clientFactory;

    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var InMemoryFindConnectorAttributesByReferenceEntityIdentifier */
    private $findConnectorReferenceEntityAttributes;

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var null|Response */
    private $attributesForReferenceEntity;

    public function __construct(
        OauthAuthenticatedClientFactory $clientFactory,
        WebClientHelper $webClientHelper,
        InMemoryFindConnectorAttributesByReferenceEntityIdentifier $findConnectorReferenceEntityAttributes,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->clientFactory = $clientFactory;
        $this->webClientHelper = $webClientHelper;
        $this->findConnectorReferenceEntityAttributes = $findConnectorReferenceEntityAttributes;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function createTextAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'description';

        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );

        $this->attributeRepository->create($textAttribute);

        $textConnectorAttribute = new ConnectorAttribute(
            $textAttribute->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'Description', 'fr_FR' => 'Description']),
            'text',
            AttributeValuePerLocale::fromBoolean($textAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($textAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                'max_characters' => $textAttribute->getMaxLength()->intValue(),
                'is_textarea' => false,
                'is_rich_text_editor' => false,
                'validation_rule' => null,
                'validation_regexp' => null
            ]
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $textConnectorAttribute
        );
    }

    public function createImageAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'photo';

        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('10'),
            AttributeAllowedExtensions::fromList(['jpg'])
        );

        $this->attributeRepository->create($imageAttribute);

        $imageAttribute = new ConnectorAttribute(
            $imageAttribute->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'Photo', 'fr_FR' => 'Photo']),
            'image',
            AttributeValuePerLocale::fromBoolean($imageAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($imageAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                'allowed_extensions' => ['jpg'],
                'max_file_size' => '10'
            ]
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $imageAttribute
        );
    }

    public function createOptionAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'nationality';

        $optionAttribute = OptionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'Nationalité', 'en_US' => 'Nationality']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute = new ConnectorAttribute(
            $optionAttribute->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'Nationality', 'fr_FR' => 'Nationalité']),
            'single_option',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            []
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $optionAttribute
        );
    }

    public function createMultiOptionAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'sales_areas';

        $optionAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['fr_FR' => 'Zones de vente', 'en_US' => 'Sales areas']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($optionAttribute);

        $optionAttribute = new ConnectorAttribute(
            $optionAttribute->getIdentifier(),
            LabelCollection::fromArray(['fr_FR' => 'Zones de vente', 'en_US' => 'Sales areas']),
            'multiple_options',
            AttributeValuePerLocale::fromBoolean($optionAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($optionAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            []
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $optionAttribute
        );
    }

    public function createSingleLinkAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'country';

        $linkAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['en_US' => 'Country', 'fr_FR' => 'Pays']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($linkAttribute);

        $linkAttribute = new ConnectorAttribute(
            $linkAttribute->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'Country', 'fr_FR' => 'Pays']),
            'reference_entity_single_link',
            AttributeValuePerLocale::fromBoolean($linkAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($linkAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(false),
            [
                "reference_entity_code" => 'country'
            ]
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $linkAttribute
        );
    }

    public function createMultiLinkAttribute(string $referenceEntityIdentifier)
    {
        $attributeIdentifier = 'designers';

        $multiLinkAttribute = OptionCollectionAttribute::create(
            AttributeIdentifier::create($referenceEntityIdentifier, $attributeIdentifier, 'test'),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray(['en_US' => 'Designers', 'fr_FR' => 'Designeurs']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );

        $this->attributeRepository->create($multiLinkAttribute);

        $multiLinkAttribute = new ConnectorAttribute(
            $multiLinkAttribute->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'Designers', 'fr_FR' => 'Designeurs']),
            'reference_entity_multiple_links',
            AttributeValuePerLocale::fromBoolean($multiLinkAttribute->hasValuePerLocale()),
            AttributeValuePerChannel::fromBoolean($multiLinkAttribute->hasValuePerChannel()),
            AttributeIsRequired::fromBoolean(true),
            [
                "reference_entity_code" => 'designer'
            ]
        );

        $this->findConnectorReferenceEntityAttributes->save(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $multiLinkAttribute
        );
    }

    /**
     * @Given /^6 attributes that structure the Brand reference entity in the PIM$/
     */
    public function attributesThatStructureTheBrandReferenceEntityInThePIM()
    {
        $referenceEntityIdentifier = 'brand';

        $this->createTextAttribute($referenceEntityIdentifier);
        $this->createImageAttribute($referenceEntityIdentifier);
        $this->createOptionAttribute($referenceEntityIdentifier);
        $this->createMultiOptionAttribute($referenceEntityIdentifier);
        $this->createSingleLinkAttribute($referenceEntityIdentifier);
        $this->createMultiLinkAttribute($referenceEntityIdentifier);

        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @When /^the connector requests the structure of the Brand reference entity from the PIM$/
     */
    public function theConnectorRequestsTheStructureOfTheBrandReferenceEntityFromThePIM()
    {
        $client = $this->clientFactory->logIn('julia');

        $this->attributesForReferenceEntity = $this->webClientHelper->requestFromFile(
            $client,
            self::REQUEST_CONTRACT_DIR ."successful_brand_reference_entity_attributes.json"
        );
    }

    /**
     * @Then /^the PIM returns the 6 attributes of the Brand reference entity$/
     */
    public function thePIMReturnsTheAttributesOfTheBrandReferenceEntity()
    {
        $this->webClientHelper->assertJsonFromFile(
            $this->attributesForReferenceEntity,
            self::REQUEST_CONTRACT_DIR . "successful_brand_reference_entity_attributes.json"
        );
    }

    /**
     * @Given /^some reference entities with some attributes$/
     */
    public function someReferenceEntitiesWithSomeAttributes()
    {
        $firstIdentifier = 'whatever_1';

        $this->createTextAttribute($firstIdentifier);

        $firstReferenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($firstIdentifier),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($firstReferenceEntity);

        $secondIdentifier = 'whatever_2';

        $this->createImageAttribute($secondIdentifier);

        $secondReferenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString($secondIdentifier),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($secondReferenceEntity);
    }
}
