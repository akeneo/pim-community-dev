<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\DBALException;

class SqlEnrichedEntityRepositoryTest extends SqlIntegrationTestCase
{
    /** @var EnrichedEntityRepositoryInterface */
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
    public function it_creates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);

        $this->repository->create($enrichedEntity);

        $enrichedEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertEnrichedEntity($enrichedEntity, $enrichedEntityFound);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_enriched_entity_with_the_same_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);

        $this->expectException(DBALException::class);
        $this->repository->create($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_updates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $this->repository->create($enrichedEntity);
        $enrichedEntity->updateLabels(LabelCollection::fromArray(['en_US' => 'Stylist', 'fr_FR' => 'Styliste']));

        $this->repository->update($enrichedEntity);

        $enrichedEntityFound = $this->repository->getByIdentifier($identifier);
        $this->assertEnrichedEntity($enrichedEntity, $enrichedEntityFound);
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_enriched_entity()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, ['en_US' => 'Designer', 'fr_FR' => 'Concepteur']);
        $enrichedEntity->updateLabels(LabelCollection::fromArray(['en_US' => 'Stylist', 'fr_FR' => 'Styliste']));

        $this->expectException(\RuntimeException::class);
        $this->repository->update($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->repository->getByIdentifier(EnrichedEntityIdentifier::fromString('unknown_identifier'));
    }

    /**
     * @param $enrichedEntityExpected
     * @param $enrichedEntityFound
     *
     */
    private function assertEnrichedEntity(
        EnrichedEntity $enrichedEntityExpected,
        EnrichedEntity $enrichedEntityFound
    ): void {
        $this->assertTrue($enrichedEntityExpected->equals($enrichedEntityFound));
        $labelCodesExpected = $enrichedEntityExpected->getLabelCodes();
        $labelCodesFound = $enrichedEntityFound->getLabelCodes();
        sort($labelCodesExpected);
        sort($labelCodesFound);
        $this->assertSame($labelCodesExpected, $labelCodesFound);
        foreach ($enrichedEntityExpected->getLabelCodes() as $localeCode) {
            $this->assertEquals($enrichedEntityExpected->getLabel($localeCode),
                $enrichedEntityFound->getLabel($localeCode));
        }
    }

    private function resetDB()
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_enriched_entity;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
    }
}
