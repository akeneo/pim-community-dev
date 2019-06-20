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
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector\FindConnectorReferenceEntityByReferenceEntityIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorReferenceEntityByReferenceEntityIdentifierTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var FindConnectorReferenceEntityByReferenceEntityIdentifierInterface*/
    private $findConnectorReferenceEntityQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->findConnectorReferenceEntityQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_reference_entity_by_reference_entity_identifier');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_a_connector_reference_entity()
    {
        $referenceEntity = $this->createDesignerReferenceEntity();

        $expectedReferenceEntity = new ConnectorReferenceEntity(
            $referenceEntity->getIdentifier(),
            LabelCollection::fromArray(['en_US' => 'designer', 'fr_FR' => 'designer']),
            Image::createEmpty()
        );

        $referenceEntityFound = $this->findConnectorReferenceEntityQuery->find(ReferenceEntityIdentifier::fromString('designer'));

        $expectedReferenceEntity = $expectedReferenceEntity->normalize();
        $foundReferenceEntity = $referenceEntityFound->normalize();

        $this->assertSame($expectedReferenceEntity, $foundReferenceEntity);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_reference_entity_found()
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('whatever');
        $referenceEntityFound = $this->findConnectorReferenceEntityQuery->find($referenceEntityIdentifier);

        $this->assertNull($referenceEntityFound);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createDesignerReferenceEntity(): ReferenceEntity
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $referenceEntity = ReferenceEntity::create(
            $referenceEntityIdentifier,
            ['en_US' => 'designer', 'fr_FR' => 'designer'],
            Image::fromFileInfo($imageInfo)
        );

        $this->referenceEntityRepository->create($referenceEntity);

        return $referenceEntity;
    }
}
