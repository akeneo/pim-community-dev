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
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityDetails;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlFindReferenceEntityDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindReferenceEntityDetailsInterface */
    private $findReferenceEntityDetails;

    public function setUp(): void
    {
        parent::setUp();

        $this->findReferenceEntityDetails = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_reference_entity_details');
        $this->resetDB();
        $this->loadReferenceEntity();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_result_for_the_given_identifier()
    {
        $result = ($this->findReferenceEntityDetails)(ReferenceEntityIdentifier::fromString('unknown_reference_entity'));
        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_finds_one_reference_entity_by_its_identifier()
    {
        $entity = ($this->findReferenceEntityDetails)(ReferenceEntityIdentifier::fromString('designer'));

        $designer = new ReferenceEntityDetails();
        $designer->identifier = ReferenceEntityIdentifier::fromString('designer');
        $designer->labels = LabelCollection::fromArray(['fr_FR' => 'Concepteur', 'en_US' => 'Designer']);

        $this->assertReferenceEntityItem($designer, $entity);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function assertReferenceEntityItem(ReferenceEntityDetails $expected, ReferenceEntityDetails $actual): void
    {
        $this->assertTrue($expected->identifier->equals($actual->identifier), 'Reference entity identifiers are not equal');
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the reference entity items are not the same'
        );
    }
}
