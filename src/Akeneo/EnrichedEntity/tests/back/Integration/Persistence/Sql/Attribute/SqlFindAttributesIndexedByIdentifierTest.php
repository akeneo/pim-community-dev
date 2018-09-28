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

namespace Akeneo\EnrichedEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\EnrichedEntity\Integration\SqlIntegrationTestCase;

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

    public function setUp()
    {
        parent::setUp();

        $this->findAttributesIndexedByIdentifier = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_attributes_indexed_by_identifier');
        $this->resetDB();
        $this->loadEnrichedEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_attributes_indexed_by_identifier_for_an_enriched_entity()
    {
        $actualAttributes = ($this->findAttributesIndexedByIdentifier)(EnrichedEntityIdentifier::fromString('designer'));

        $expectedAttributes = [
            'name_designer_test'             => $this->name,
            'email_designer_test'            => $this->email,
            'long_description_designer_test' => $this->longDescription,
            'regex_designer_test'            => $this->customRegex,
            'image_designer_test'            => $this->imageAttribute,
        ];
        $this->assertCount(5, $actualAttributes);
        foreach ($expectedAttributes as $expectedIdentifier => $expectedAttribute) {
            $this->assertArrayHasKey($expectedIdentifier, $actualAttributes);
            $this->assertSame($expectedAttribute->normalize(), $actualAttributes[$expectedIdentifier]->normalize());
        }
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_the_enriched_entity_does_not_have_any_attributes()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('brand');
        $attributeDetails = ($this->findAttributesIndexedByIdentifier)($enrichedEntityIdentifier);
        $this->assertCount(0, $attributeDetails);
    }

    private function resetDB(): void
    {
        $this->get('akeneoenriched_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntitiesAndAttributes(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntityFull = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $enrichedEntityEmpty = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ],
            Image::createEmpty()
        );
        $enrichedEntityRepository->create($enrichedEntityFull);
        $enrichedEntityRepository->create($enrichedEntityEmpty);

        $attributesRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $this->name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
        $this->email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );
        $this->customRegex = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'regex', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('regex'),
            LabelCollection::fromArray(['en_US' => 'Regex']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::REGULAR_EXPRESSION),
            AttributeRegularExpression::fromString('/\w+/')
        );
        $this->longDescription = TextAttribute::createTextarea(
            AttributeIdentifier::create('designer', 'long_description', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('long_description'),
            LabelCollection::fromArray(['en_US' => 'Long description']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeIsRichTextEditor::fromBoolean(true)
        );
        $this->imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(4),
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
    }
}
