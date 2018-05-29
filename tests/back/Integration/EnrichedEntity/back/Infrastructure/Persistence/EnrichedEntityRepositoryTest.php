<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\InMemory;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoEnterprise\Test\Acceptance\EnrichedEntity\InMemoryEnrichedEntityRepository;

class InMemoryEnrichedEntityRepositoryTest extends TestCase
{
    /** @var array */
    protected $repositories;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');

        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_when_there_is_no_enriched_entity()
    {
        $this->assertEmpty($this->repository->all());
    }

    /**
     * @test
     */
    public function it_adds_enriched_entity_and_returns_it() {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::define($identifier, LabelCollection::fromArray([]));

        $this->repository->add($enrichedEntity);

        $enrichedEntityFound = $this->repository->findOneByIdentifier($identifier);
        $this->assertTrue($enrichedEntity->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_is_not_found()
    {
        $enrichedEntity = $this->repository->findOneByIdentifier(EnrichedEntityIdentifier::fromString('unknown_identifier'));
        $this->assertNull($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_returns_all_the_enriched_entities_added()
    {
        $identifier1 = EnrichedEntityIdentifier::fromString('identifier1');
        $enrichedEntity1 = EnrichedEntity::define($identifier1, LabelCollection::fromArray([]));
        $identifier2 = EnrichedEntityIdentifier::fromString('identifier2');
        $enrichedEntity2 = EnrichedEntity::define($identifier2, LabelCollection::fromArray([]));

        $this->repository->add($enrichedEntity1);
        $this->repository->add($enrichedEntity2);
        $enrichedEntitiesFound = $this->repository->all();

        $this->assertEnrichedEntityList([$enrichedEntity1, $enrichedEntity2], $enrichedEntitiesFound);
    }

    /**
     * @param EnrichedEntity[] $enrichedEntities
     * @param EnrichedEntity[] $enrichedEntitiesFound
     */
    private function assertEnrichedEntityList($enrichedEntities, $enrichedEntitiesFound): void
    {
        foreach ($enrichedEntities as $enrichedEntity) {
            $isFound = false;
            foreach ($enrichedEntitiesFound as $enrichedEntityFound) {
                if (!$isFound && $enrichedEntityFound->equals($enrichedEntity)) {
                    $isFound = true;
                }

            }
            $this->assertTrue($isFound,
                sprintf('The enriched entity with identifier %s was not found', (string) $enrichedEntity->getIdentifier())
            );
        }
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return null;
    }

    private function resetDB()
    {

    }
}
