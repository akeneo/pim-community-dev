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
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\EnrichedEntity\Integration\SqlIntegrationTestCase;

class SqlGenerateEmptyValuesTest extends SqlIntegrationTestCase
{
    /** @var FindValueKeyCollectionInterface */
    private $generateEmptyValues;

    private $order = 0;

    public function setUp()
    {
        parent::setUp();

        $this->generateEmptyValues = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.generate_empty_values');
        $this->resetDB();
        $this->loadEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_returns_all_empty_values_possible_for_a_given_enriched_entity()
    {
        $designer = EnrichedEntityIdentifier::fromString('designer');
        $image = $this->loadAttribute('designer', 'image', false, false);
        $name = $this->loadAttribute('designer', 'name', false, true);
        $age = $this->loadAttribute('designer', 'age', true, false);
        $weight = $this->loadAttribute('designer', 'weigth', true, true);
        $emptyValues = ($this->generateEmptyValues)($designer);

        $this->assertCount(11, $emptyValues);
        $this->assertArrayHasKey(sprintf('%s', $image->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_en_US', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_de_DE', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_fr_FR', $name->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_mobile', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_print', $age->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_print_en_US', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_mobile_de_DE', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier()), $emptyValues);
        $this->assertArrayHasKey(sprintf('%s_ecommerce_en_US', $weight->getIdentifier()), $emptyValues);

        $this->assertSame([
            'data' => null,
            'locale' => null,
            'channel' => null,
            'attribute' => $image->normalize(),
        ], $emptyValues[sprintf('%s', $image->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => 'de_DE',
            'channel' => null,
            'attribute' => $name->normalize(),
        ], $emptyValues[sprintf('%s_de_DE', $name->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => null,
            'channel' => 'mobile',
            'attribute' => $age->normalize(),
        ], $emptyValues[sprintf('%s_mobile', $age->getIdentifier())]);

        $this->assertSame([
            'data' => null,
            'locale' => 'fr_FR',
            'channel' => 'ecommerce',
            'attribute' => $weight->normalize(),
        ], $emptyValues[sprintf('%s_ecommerce_fr_FR', $weight->getIdentifier())]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoenriched_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntity(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }

    private function loadAttribute(string $enrichedEntityIdentifier, string $attributeCode, bool $hasValuePerChannel, bool $hasValuePerLocale): AbstractAttribute
    {
        $attributeRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.repository.attribute');
        $identifier = $attributeRepository->nextIdentifier(
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            AttributeCode::fromString($attributeCode)
        );

        $attribute = TextAttribute::createText(
            $identifier,
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            AttributeCode::fromString($attributeCode),
            LabelCollection::fromArray(['fr_FR' => 'dummy label']),
            AttributeOrder::fromInteger($this->order++),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($hasValuePerChannel),
            AttributeValuePerLocale::fromBoolean($hasValuePerLocale),
            AttributeMaxLength::fromInteger(25),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $attributeRepository->create($attribute);

        return $attribute;
    }
}
