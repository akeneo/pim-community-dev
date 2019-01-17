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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlFindAttributesIndexedByIdentifierTest extends SqlIntegrationTestCase
{
    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var AbstractAttribute */
    private $name;

    /** @var AbstractAttribute */
    private $email;

    /** @var AbstractAttribute */
    private $customRegex;

    /** @var AbstractAttribute */
    private $longDescription;

    /** @var AbstractAttribute */
    private $imageAttribute;

    /** @var AbstractAttribute */
    private $attributeAsLabel;

    /** @var AbstractAttribute */
    private $attributeAsImage;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributesIndexedByIdentifier = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_attributes_indexed_by_identifier');
        $this->resetDB();
        $this->loadReferenceEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_indexed_by_identifier_for_a_reference_entity()
    {
        $actualAttributes = ($this->findAttributesIndexedByIdentifier)(ReferenceEntityIdentifier::fromString('designer'));

        $expectedAttributes = [
            'name_designer_test'             => $this->name,
            'email_designer_test'            => $this->email,
            'long_description_designer_test' => $this->longDescription,
            'regex_designer_test'            => $this->customRegex,
            'image_designer_test'            => $this->imageAttribute,
            $this->attributeAsLabel->getIdentifier()->normalize() => $this->attributeAsLabel,
            $this->attributeAsImage->getIdentifier()->normalize() => $this->attributeAsImage,
        ];
        $this->assertCount(7, $actualAttributes);
        foreach ($expectedAttributes as $expectedIdentifier => $expectedAttribute) {
            $this->assertArrayHasKey($expectedIdentifier, $actualAttributes);
            $this->assertSame($expectedAttribute->normalize(), $actualAttributes[$expectedIdentifier]->normalize());
        }
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_reference_entity_does_not_have_any_attributes()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $attributeDetails = ($this->findAttributesIndexedByIdentifier)($referenceEntityIdentifier);
        $this->assertCount(0, $attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntitiesAndAttributes(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityWithAttributes = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityWithoutAttributes = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntityWithAttributes);
        $referenceEntityWithAttributes = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('designer'));
        $referenceEntityRepository->create($referenceEntityWithoutAttributes);
        $referenceEntityWithoutAttributes = $referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString('brand'));

        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );
        $this->customRegex = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'regex', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Regex']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $this->longDescription = TextAttribute::createTextarea(
            AttributeIdentifier::create('designer', 'long_description', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('long_description'),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            AttributeOrder::fromInteger(5),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(6),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );
        $attributesRepository->create($this->name);
        $attributesRepository->create($this->email);
        $attributesRepository->create($this->customRegex);
        $attributesRepository->create($this->longDescription);
        $attributesRepository->create($this->imageAttribute);

        $attributesRepository->deleteByIdentifier($referenceEntityWithoutAttributes->getAttributeAsLabelReference()->getIdentifier());
        $attributesRepository->deleteByIdentifier($referenceEntityWithoutAttributes->getAttributeAsImageReference()->getIdentifier());

        $this->attributeAsLabel = $attributesRepository->getByIdentifier($referenceEntityWithAttributes->getAttributeAsLabelReference()->getIdentifier());
        $this->attributeAsImage = $attributesRepository->getByIdentifier($referenceEntityWithAttributes->getAttributeAsImageReference()->getIdentifier());
    }
}
