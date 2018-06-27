<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Infrastructure\Persistence\InMemory;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use Akeneo\EnrichedEntity\back\Domain\Repository\EntityNotFoundException;
use AkeneoEnterprise\Test\Acceptance\EnrichedEntity\InMemoryEnrichedEntityRepository;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use PHPUnit\Framework\TestCase;

class InMemoryEnrichedEntityRepositoryTest extends TestCase
{
    /** @var EnrichedEntityRepository */
    private $enrichedEntityRepository;

    public function setup()
    {
        $this->enrichedEntityRepository = new InMemoryEnrichedEntityRepository();
    }

    /**
     * @test
     */
    public function it_save_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, []);

        $this->enrichedEntityRepository->save($enrichedEntity);

        $enrichedEntityFound = $this->enrichedEntityRepository->getByIdentifier($identifier);
        $this->assertTrue($enrichedEntity->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EntityNotFoundException::class);
        $this->enrichedEntityRepository->getByIdentifier(
            EnrichedEntityIdentifier::fromString('unknown_identifier')
        );
    }

    /**
     * @param EnrichedEntity[] $EnrichedEntitys
     * @param EnrichedEntity[] $EnrichedEntitysFound
     */
    private function assertEnrichedEntityList(array $EnrichedEntitys, array $EnrichedEntitysFound): void
    {
        foreach ($EnrichedEntitys as $enrichedEntity) {
            $isFound = false;
            foreach ($EnrichedEntitysFound as $enrichedEntityFound) {
                if (!$isFound && $enrichedEntityFound->equals($enrichedEntity)) {
                    $isFound = true;
                }

            }
            $this->assertTrue(
                $isFound,
                sprintf('The enriched entity with identifier %s was not found', (string) $enrichedEntity->getIdentifier())
            );
        }
    }
}
