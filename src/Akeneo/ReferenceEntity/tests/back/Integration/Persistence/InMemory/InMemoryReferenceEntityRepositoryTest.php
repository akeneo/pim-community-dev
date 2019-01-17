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

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryReferenceEntityRepositoryTest extends TestCase
{
    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function setup()
    {
        $this->referenceEntityRepository = new InMemoryReferenceEntityRepository(
            new EventDispatcher()
        );
    }

    /**
     * @test
     */
    public function it_creates_a_reference_entity_and_returns_it()
    {
        $identifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $referenceEntity = ReferenceEntity::create($identifier, [], Image::createEmpty());

        $this->referenceEntityRepository->create($referenceEntity);

        $referenceEntityFound = $this->referenceEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($referenceEntity->equals($referenceEntityFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_reference_entity_with_the_same_identifier()
    {
        $identifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $referenceEntity = ReferenceEntity::create($identifier, [], Image::createEmpty());
        $this->referenceEntityRepository->create($referenceEntity);

        $this->expectException(\RuntimeException::class);
        $this->referenceEntityRepository->create($referenceEntity);
    }

    /**
     * @test
     */
    public function it_updates_a_reference_entity_and_returns_it()
    {
        $identifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $referenceEntity = ReferenceEntity::create($identifier, [], Image::createEmpty());
        $this->referenceEntityRepository->create($referenceEntity);
        $referenceEntity->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->referenceEntityRepository->update($referenceEntity);

        $referenceEntityFound = $this->referenceEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($referenceEntity->equals($referenceEntityFound));
    }

    /**
     * @test
     */
    public function it_returns_all_reference_entities()
    {
        $designer = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('designer'), [], Image::createEmpty());
        $brand = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('brand'), [], Image::createEmpty());
        $this->referenceEntityRepository->create($designer);
        $this->referenceEntityRepository->create($brand);

        $referenceEntities = iterator_to_array($this->referenceEntityRepository->all());
        Assert::assertSame($designer, $referenceEntities[0]);
        Assert::assertSame($brand, $referenceEntities[1]);
    }

    /**
     * @test
     */
    public function it_tells_if_the_repository_has_the_reference_entity()
    {
        $anotherIdentifier = ReferenceEntityIdentifier::fromString('another_identifier');
        $identifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $this->referenceEntityRepository->create(ReferenceEntity::create($identifier, [], Image::createEmpty()));
        Assert::assertTrue($this->referenceEntityRepository->hasReferenceEntity($identifier));
        Assert::assertFalse($this->referenceEntityRepository->hasReferenceEntity($anotherIdentifier));
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_reference_entity()
    {
        $identifier = ReferenceEntityIdentifier::fromString('reference_entity_identifier');
        $referenceEntity = ReferenceEntity::create($identifier, [], Image::createEmpty());
        $this->referenceEntityRepository->create($referenceEntity);
        $referenceEntity->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->referenceEntityRepository->update($referenceEntity);

        $referenceEntityFound = $this->referenceEntityRepository->getByIdentifier($identifier);
        Assert::assertTrue($referenceEntity->equals($referenceEntityFound));
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->referenceEntityRepository->getByIdentifier(
            ReferenceEntityIdentifier::fromString('unknown_identifier')
        );
    }

    /**
     * @test
     */
    public function it_deletes_a_reference_entity_given_an_identifier()
    {
        $identifier = ReferenceEntityIdentifier::fromString('identifier');
        $referenceEntity = ReferenceEntity::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty()
        );
        $this->referenceEntityRepository->create($referenceEntity);

        $this->referenceEntityRepository->deleteByIdentifier($identifier);

        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->referenceEntityRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_reference_entity()
    {
        $identifier = ReferenceEntityIdentifier::fromString('unknown');

        $this->expectException(ReferenceEntityNotFoundException::class);
        $this->referenceEntityRepository->deleteByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_counts_the_total_of_reference_entities()
    {
        $this->assertEquals(0, $this->referenceEntityRepository->count());

        $refOne = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('one'), ['en_US' => 'one'], Image::createEmpty());
        $refTwo = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('two'), ['en_US' => 'two'], Image::createEmpty());
        $refThree = ReferenceEntity::create(ReferenceEntityIdentifier::fromString('three'), ['en_US' => 'three'], Image::createEmpty());
        $this->referenceEntityRepository->create($refOne);
        $this->referenceEntityRepository->create($refTwo);
        $this->referenceEntityRepository->create($refThree);
        $this->assertEquals(3, $this->referenceEntityRepository->count());
    }
}
