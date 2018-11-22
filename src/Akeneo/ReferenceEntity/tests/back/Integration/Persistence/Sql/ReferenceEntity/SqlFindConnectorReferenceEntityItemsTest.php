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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\ConnectorReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityItemsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityQuery;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorReferenceEntityItemsTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var FindConnectorReferenceEntityItemsInterface*/
    private $findConnectorReferenceEntityItems;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->findConnectorReferenceEntityItems = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_reference_entity_items');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_connector_reference_entity_items_without_search_after()
    {
        $referenceEntities = [];

        for ($i = 1; $i <= 3; $i++) {
            $referenceEntity = $this->createReferenceEntity(sprintf('reference_entity_%s', $i));
            $referenceEntities[] = new ConnectorReferenceEntity(
                $referenceEntity->getIdentifier(),
                LabelCollection::fromArray(['en_US' => sprintf('reference_entity_%s', $i)]),
                Image::createEmpty()
            );
        }

        $findReferenceEntitiesQuery = ReferenceEntityQuery::createPaginatedQuery(3, null);
        $foundReferenceEntities = ($this->findConnectorReferenceEntityItems)($findReferenceEntitiesQuery);

        $normalizedReferenceEntities = [];
        foreach ($referenceEntities as $referenceEntity) {
            $normalizedReferenceEntities[] = $referenceEntity->normalize();
        }

        $normalizedFoundReferenceEntities = [];
        foreach ($foundReferenceEntities as $referenceEntity) {
            $normalizedFoundReferenceEntities[] = $referenceEntity->normalize();
        }

        $this->assertSame($normalizedReferenceEntities, $normalizedFoundReferenceEntities);
    }

    /**
     * @test
     */
    public function it_finds_connector_reference_entities_after_identifier()
    {
        $referenceEntities = [];

        for ($i = 1; $i <= 7; $i++) {
            $referenceEntity = $this->createReferenceEntity(sprintf('reference_entity_%s', $i));
            $referenceEntities[] = new ConnectorReferenceEntity(
                $referenceEntity->getIdentifier(),
                LabelCollection::fromArray(['en_US' => sprintf('reference_entity_%s', $i)]),
                Image::createEmpty()
            );
        }

        $searchAfterIdentifier = ReferenceEntityIdentifier::fromString('reference_entity_3');
        $findReferenceEntitiesQuery = ReferenceEntityQuery::createPaginatedQuery(3, $searchAfterIdentifier);
        $foundReferenceEntities = ($this->findConnectorReferenceEntityItems)($findReferenceEntitiesQuery);

        $normalizedReferenceEntities = [];
        foreach ($referenceEntities as $referenceEntity) {
            $normalizedReferenceEntities[] = $referenceEntity->normalize();
        }

        $normalizedFoundReferenceEntities = [];
        foreach ($foundReferenceEntities as $referenceEntity) {
            $normalizedFoundReferenceEntities[] = $referenceEntity->normalize();
        }

        $this->assertSame(array_slice($normalizedReferenceEntities, 3, 3), $normalizedFoundReferenceEntities);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_reference_entities_found()
    {
        $findReferenceEntitiesQuery = ReferenceEntityQuery::createPaginatedQuery(3, null);
        $foundReferenceEntities = ($this->findConnectorReferenceEntityItems)($findReferenceEntitiesQuery);

        $this->assertSame([], $foundReferenceEntities);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createReferenceEntity(string $rawIdentifier): ReferenceEntity
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($rawIdentifier);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(sprintf('image_%s', $rawIdentifier))
            ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            ['en_US' => $rawIdentifier],
            Image::fromFileInfo($imageInfo)
        );

        $this->referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }
}
