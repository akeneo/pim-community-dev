<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\UpdatedFamily;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetFamiliesWithUpdatedAttributesListQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;

final class GetFamiliesWithUpdatedAttributesListQueryIntegration extends TestCase
{
    const ATTRIBUTES = ['name', 'description', 'color'];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::ATTRIBUTES as $attributeCode) {
            $this->createAttribute($attributeCode);
        }
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_retrieves_the_families_with_updated_attributes_list_since_a_given_date()
    {
        $now = new \DateTimeImmutable('2020-06-21 14:32:56');
        $expectedUpdatedFamilies = [];

        $expectedUpdatedFamilies[] = $this->givenAFamilyWithOneRemovedAttribute($now);
        $expectedUpdatedFamilies[] = $this->givenAFamilyWithOneAddedAttribute($now);
        $expectedUpdatedFamilies[] = $this->givenAFamilyWithSeveralRemovedAttributes($now);
        $this->givenAFamilyWithAddedAttributesForTooLong($now);
        $this->givenAFamilyWithoutChangesOnTheirAttributes($now);

        $updatedFamilies = $this->get(GetFamiliesWithUpdatedAttributesListQuery::class)->updatedSince($now);

        $this->assertUpdatedFamiliesEquals($expectedUpdatedFamilies, $updatedFamilies);
    }

    private function givenAFamilyWithOneRemovedAttribute(\DateTimeImmutable $now): UpdatedFamily
    {
        $family = $this->createFamily('FamilyWithOneRemovedAttribute', self::ATTRIBUTES, $now->modify('-1 second'));

        $newFamilyData = ['attributes' => array_slice(self::ATTRIBUTES, 1)];
        $this->updateFamily($family, $newFamilyData, $now);

        // Add more recent update to ensure that only changes on attributes are taken into account
        $this->updateFamily($family, ['labels' => ['en_US' => 'A family']], $now->modify('+1 hour'));

        return new UpdatedFamily($family->getId(), $now);
    }

    private function givenAFamilyWithSeveralRemovedAttributes(\DateTimeImmutable $now): UpdatedFamily
    {
        $family = $this->createFamily('FamilyWithSeveralRemovedAttributes', self::ATTRIBUTES, $now->modify('-10 second'));

        $lastUpdatedAt = $now->modify('+12 seconds');
        $newFamilyData = ['attributes' => array_slice(self::ATTRIBUTES, 1)];
        $this->updateFamily($family, $newFamilyData, $lastUpdatedAt);

        $newFamilyData = ['attributes' => array_slice(self::ATTRIBUTES, 2)];
        $this->updateFamily($family, $newFamilyData, $lastUpdatedAt->modify('-1 second'));

        return new UpdatedFamily($family->getId(), $lastUpdatedAt);
    }

    private function givenAFamilyWithOneAddedAttribute(\DateTimeImmutable $now): UpdatedFamily
    {
        $this->createAttribute('size');
        $family = $this->createFamily('FamilyWithOneAddedAttribute', self::ATTRIBUTES, $now->modify('-1 second'));

        $lastUpdatedAt = $now->modify('+42 seconds');
        $newFamilyData = ['attributes' => array_merge(self::ATTRIBUTES, ['size'])];
        $this->updateFamily($family, $newFamilyData, $lastUpdatedAt);

        return new UpdatedFamily($family->getId(), $lastUpdatedAt);
    }

    private function givenAFamilyWithAddedAttributesForTooLong(\DateTimeImmutable $now): void
    {
        $this->createAttribute('height');
        $family = $this->createFamily('FamilyWithAddedAttributesForTooLong', self::ATTRIBUTES, $now->modify('-1 minute'));

        $newFamilyData = ['attributes' => array_merge(self::ATTRIBUTES, ['height'])];
        $this->updateFamily($family, $newFamilyData, $now->modify('-1 second'));
    }

    private function givenAFamilyWithoutChangesOnTheirAttributes(\DateTimeImmutable $now): void
    {
        $this->createFamily('AFamilyWithoutChangesOnTheirAttributes', [], $now->modify('-10 second'));
    }

    private function createAttribute(string $code): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function createFamily(string $code, array $attributes, \DateTimeImmutable $createdAt): FamilyInterface
    {
        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $code,
                'attributes' => $attributes
            ]);

        $this->get('pim_catalog.saver.family')->save($family);
        $this->updateFamilyVersionDate($family->getId(), $createdAt);

        return $family;
    }

    private function updateFamily(FamilyInterface $family, array $data, \DateTimeImmutable $updatedAt): void
    {
        $lastVersionId = $this->getLastFamilyVersionId($family->getId());

        $this->get('pim_catalog.updater.family')->update($family, $data);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->updateFamilyVersionDate($family->getId(), $updatedAt, $lastVersionId);
    }

    private function getLastFamilyVersionId(int $familyId): int
    {
        $query = <<<SQL
SELECT MAX(id) FROM pim_versioning_version 
WHERE resource_name = :familyClass AND resource_id = :familyId
SQL;

        $stmt = $this->get('database_connection')->executeQuery($query, [
            'familyClass' => $this->getParameter('pim_catalog.entity.family.class'),
            'familyId' => $familyId,
        ]);

        return intval($stmt->fetchColumn());
    }

    private function updateFamilyVersionDate(int $familyId, \DateTimeImmutable $updatedAt, ?int $lastVersionId = null): void
    {
        $query = <<<SQL
UPDATE pim_versioning_version SET logged_at = :updatedAt
WHERE resource_name = :familyClass AND resource_id = :familyId AND id > :lastId
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'updatedAt' => $updatedAt->format(Clock::TIME_FORMAT),
            'familyClass' => $this->getParameter('pim_catalog.entity.family.class'),
            'familyId' => $familyId,
            'lastId' => $lastVersionId ?? 0,
        ]);
    }

    private function assertUpdatedFamiliesEquals(array $expectedUpdatedFamilies, array $updatedFamilies): void
    {
        $formatUpdatedFamilies = function (UpdatedFamily $updatedFamily) {
            return [
                'id' => $updatedFamily->getFamilyId(),
                'updated_at' => $updatedFamily->updatedAt()->format(Clock::TIME_FORMAT),
            ];
        };

        $expectedUpdatedFamilies = array_map($formatUpdatedFamilies, $expectedUpdatedFamilies);
        $updatedFamilies = array_map($formatUpdatedFamilies, $updatedFamilies);

        $this->assertEqualsCanonicalizing($expectedUpdatedFamilies, $updatedFamilies);
    }
}
