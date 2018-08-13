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

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindAttributeNextOrderTest extends SqlIntegrationTestCase
{
    /** @var FindAttributeNextOrderInterface */
    private $findAttributeNextOrder;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributeNextOrder = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_attribute_next_order');
        $this->resetDB();
        $this->loadEnrichedEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_next_order_if_the_enriched_entity_already_have_attributes()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('designer');

        $nextOrder = $this->findAttributeNextOrder->withEnrichedEntityIdentifier($enrichedEntityIdentifier);

        $this->assertEquals(1, $nextOrder);
    }

    /**
     * @test
     */
    public function it_returns_zero_if_the_enriched_entity_does_not_have_any_attribute_yet()
    {
        $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString('brand');

        $nextOrder = $this->findAttributeNextOrder->withEnrichedEntityIdentifier($enrichedEntityIdentifier);

        $this->assertEquals(0, $nextOrder);
    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntitiesAndAttributes(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $attributesRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');

        $enrichedEntityFull = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityFull);

        $identifier = AttributeIdentifier::create('designer', 'name');
        $textAttribute = TextAttribute::create(
            $identifier,
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155)
        );
        $attributesRepository->create($textAttribute);

        $enrichedEntityEmpty = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('brand'),
            [
                'fr_FR' => 'Marque',
                'en_US' => 'Brand',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityEmpty);
    }
}
