<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Sql;

use Akeneo\EnrichedEntity\back\Domain\Query\SqlFindAttributeNextOrderInterface;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;

class SqlFindAttributeNextOrderTest extends SqlIntegrationTestCase
{
    /** @var SqlFindAttributeNextOrderInterface */
    private $findAttributeNextOrder;

    public function setUp()
    {
        parent::setUp();

        $this->findAttributeNextOrder = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_record_details');
        $this->resetDB();
        $this->loadEnrichedEntitiesAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_the_next_order_if_the_enriched_entity_already_have_attributes()
    {

    }

    /**
     * @test
     */
    public function it_returns_zero_if_the_enriched_entity_does_not_have_any_attribute_yet()
    {

    }

    private function resetDB(): void
    {
        $this->get('akeneo_ee_integration_tests.helper.database_helper')->resetDatabase();
    }

    private function loadEnrichedEntitiesAndAttributes(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntityAttributesRepo = $this->get('akeneo_enrichedentity.infrastructure.persistence.attribute');

        $enrichedEntityFull = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityFull);

        // mettre des attributs dedans

        $enrichedEntityEmpty = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntityEmpty);

    }
}
