<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\InMemory;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class SqlEnrichedEntityRepositoryTest extends TestCase
{
    /** @var EnrichedEntityRepository */
    private $repository;

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
        $enrichedEntity = EnrichedEntity::define($identifier, LabelCollection::fromArray(['en_US' => 'Designer', 'fr_FR' => 'Concepteur']));

        $this->repository->add($enrichedEntity);

        $enrichedEntityFound = $this->repository->findOneByIdentifier($identifier);
        $this->assertEnrichedEntity($enrichedEntity, $enrichedEntityFound);
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
        $identifier1 = EnrichedEntityIdentifier::fromString('designer');
        $enrichedEntity1 = EnrichedEntity::define($identifier1, LabelCollection::fromArray(['en_US' => 'Designer', 'fr_FR' => 'Concepteur']));
        $identifier2 = EnrichedEntityIdentifier::fromString('fabricant');
        $enrichedEntity2 = EnrichedEntity::define($identifier2, LabelCollection::fromArray(['en_US' => 'Manufacturer', 'fr_FR' => 'Fabricant']));
        $identifier3 = EnrichedEntityIdentifier::fromString('other');
        $enrichedEntity3 = EnrichedEntity::define($identifier3, LabelCollection::fromArray([]));

        $this->repository->add($enrichedEntity1);
        $this->repository->add($enrichedEntity2);
        $this->repository->add($enrichedEntity3);
        $enrichedEntitiesFound = $this->repository->all();

        $this->assertEnrichedEntityList([$enrichedEntity1, $enrichedEntity2, $enrichedEntity3], $enrichedEntitiesFound);
    }

    /**
     * @param EnrichedEntity[] $enrichedEntitiesExpected
     * @param EnrichedEntity[] $enrichedEntitiesFound
     */
    private function assertEnrichedEntityList($enrichedEntitiesExpected, $enrichedEntitiesFound): void
    {
        foreach ($enrichedEntitiesExpected as $enrichedEntityExpected) {
            $isFound = false;
            foreach ($enrichedEntitiesFound as $enrichedEntityFound) {
                if ($enrichedEntityFound->equals($enrichedEntityExpected)) {
                    $isFound = true;
                    $this->assertEnrichedEntity($enrichedEntityExpected, $enrichedEntityFound);
                }
            }
            $this->assertTrue(
                $isFound,
                sprintf(
                    'The enriched entity with identifier "%s" was not found',
                    (string) $enrichedEntityExpected->getIdentifier()
                )
            );
        }
    }

    /**
     * @param $enrichedEntityExpected
     * @param $enrichedEntityFound
     *
     */
    private function assertEnrichedEntity(EnrichedEntity $enrichedEntityExpected, EnrichedEntity $enrichedEntityFound): void
    {
        $this->assertTrue($enrichedEntityExpected->equals($enrichedEntityFound));
        $labelCodesExpected = $enrichedEntityExpected->getLabelCodes();
        $labelCodesFound = $enrichedEntityFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach($enrichedEntityExpected->getLabelCodes() as $localeCode) {
            $this->assertEquals($enrichedEntityExpected->getLabel($localeCode), $enrichedEntityFound->getLabel($localeCode));
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
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_enriched_entity;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
    }
}
