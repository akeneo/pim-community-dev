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

namespace Akeneo\EnrichedEntity\Integration\Persistence\InMemory;

use Akeneo\EnrichedEntity\Common\Fake\InMemoryEnrichedEntityRepository;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Image;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class InMemoryEnrichedEntityRepositoryTest extends TestCase
{
    /** @var EnrichedEntityRepositoryInterface */
    private $enrichedEntityRepository;

    public function setup()
    {
        $this->enrichedEntityRepository = new InMemoryEnrichedEntityRepository();
    }

    /**
     * @test
     */
    public function it_creates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, [], Image::createEmpty());

        $this->enrichedEntityRepository->create($enrichedEntity);

        $enrichedEntityFound = $this->enrichedEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($enrichedEntity->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_enriched_entity_with_the_same_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, [], Image::createEmpty());
        $this->enrichedEntityRepository->create($enrichedEntity);

        $this->expectException(\RuntimeException::class);
        $this->enrichedEntityRepository->create($enrichedEntity);
    }

    /**
     * @test
     */
    public function it_updates_an_enriched_entity_and_returns_it()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, [], Image::createEmpty());
        $this->enrichedEntityRepository->create($enrichedEntity);
        $enrichedEntity->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->enrichedEntityRepository->update($enrichedEntity);

        $enrichedEntityFound = $this->enrichedEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($enrichedEntity->equals($enrichedEntityFound));
    }

    /**
     * @test
     */
    public function it_tells_if_the_repository_has_the_enriched_entity()
    {
        $anotherIdentifier = EnrichedEntityIdentifier::fromString('another_identifier');
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $this->enrichedEntityRepository->create(EnrichedEntity::create($identifier, [], Image::createEmpty()));
        Assert::assertTrue($this->enrichedEntityRepository->hasRecord($identifier));
        Assert::assertFalse($this->enrichedEntityRepository->hasRecord($anotherIdentifier));
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_enriched_entity()
    {
        $identifier = EnrichedEntityIdentifier::fromString('enriched_entity_identifier');
        $enrichedEntity = EnrichedEntity::create($identifier, [], Image::createEmpty());
        $this->enrichedEntityRepository->create($enrichedEntity);
        $enrichedEntity->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->enrichedEntityRepository->update($enrichedEntity);

        $enrichedEntityFound = $this->enrichedEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($enrichedEntity->equals($enrichedEntityFound));
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

    /**
     * @test
     */
    public function it_deletes_an_enriched_entity_given_an_identifier()
    {
        $identifier = EnrichedEntityIdentifier::fromString('identifier');
        $enrichedEntity = EnrichedEntity::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty()
        );
        $this->enrichedEntityRepository->create($enrichedEntity);

        $this->enrichedEntityRepository->deleteByIdentifier($identifier);

        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->enrichedEntityRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_enriched_entity()
    {
        $identifier = EnrichedEntityIdentifier::fromString('unknown');

        $this->expectException(EnrichedEntityNotFoundException::class);
        $this->enrichedEntityRepository->deleteByIdentifier($identifier);
    }
}
