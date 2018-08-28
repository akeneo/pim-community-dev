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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextArea;
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
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

class SqlAttributeRepositoryTest extends SqlIntegrationTestCase
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');
        $this->resetDB();
        $this->insertEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_text_and_returns_it()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedAttribute = TextAttribute::createTextArea(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_creates_an_attribute_of_type_image_and_returns_it()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $expectedAttribute = ImageAttribute::create(
            $identifier,
            $enrichedEntityIdentifier,
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.12'),
            AttributeAllowedExtensions::fromList(['pdf', 'png'])
        );

        $this->attributeRepository->create($expectedAttribute);

        $actualAttribute = $this->attributeRepository->getByIdentifier($identifier);
        $this->assertAttribute($expectedAttribute, $actualAttribute);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_attribute_with_the_same_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $attribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($attribute);

        $this->expectException(DBALException::class);
        $this->attributeRepository->create($attribute);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('designer', 'bio'));
    }

    /**
     * @test
     */
    public function it_throws_if_the_attribute_type_is_not_known()
    {
        $this->insertRowWithUnsupportedType();
        $this->expectException(\LogicException::class);
        $this->attributeRepository->getByIdentifier(AttributeIdentifier::create('designer', 'age'));
    }

    /**
     * @test
     */
    public function it_deletes_an_attribute_by_its_identifier()
    {
        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = $this->createAttributeWithIdentifier($identifier);
        $this->attributeRepository->create($textAttribute);

        $this->attributeRepository->deleteByIdentifier($identifier);

        $this->expectException(AttributeNotFoundException::class);
        $this->attributeRepository->getByIdentifier($identifier);
    }

    private function assertAttribute(
        AbstractAttribute $expectedAttribute,
        AbstractAttribute $actualAttribute
    ): void {
        $this->assertSame($expectedAttribute->normalize(), $actualAttribute->normalize());
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function insertEnrichedEntity(): void
    {
        $repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer'
            ]
        );
        $repository->create($enrichedEntity);
    }

    private function insertRowWithUnsupportedType()
    {
        $sqlConnection = $this->get('database_connection');
        $query = <<<SQL
        INSERT INTO akeneo_enriched_entity_attribute (
            identifier,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        )
        VALUES (
            :identifier,
            :enriched_entity_identifier,
            :labels,
            :attribute_type,
            :attribute_order,
            :is_required,
            :value_per_channel,
            :value_per_locale,
            :additional_properties
        );
SQL;
        $assertRows =$sqlConnection->executeUpdate(
            $query,
            [
                'identifier'                 => 'age',
                'enriched_entity_identifier' => 'designer',
                'labels'                     => '{}',
                'attribute_type'             => 'UNSUPPORTED_ATTRIBUTE_TYPE',
                'attribute_order'            => 1,
                'is_required'                   => 0,
                'value_per_channel'          => 0,
                'value_per_locale'           => 0,
                'additional_properties'      => '{}',
            ]
        );
        $this->assertEquals(1, $assertRows);
    }

    private function createAttributeWithIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        return TextAttribute::createText(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::emptyRegularExpression()
        );
    }
}
