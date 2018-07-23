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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepository;
use Akeneo\EnrichedEntity\tests\back\Common\InMemoryEnrichedEntityRepository;
use PHPUnit\Framework\Assert;
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
    public function it_saves_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, []);

        $this->enrichedEntityRepository->save($enrichedEntity);

        $enrichedEntityFound = $this->enrichedEntityRepository->getByIdentifier($identifier);
        Assert::isTrue($enrichedEntity->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->enrichedEntityRepository->getByIdentifier(
            EnrichedEntityIdentifier::fromString('unknown_identifier')
        );
    }
}
