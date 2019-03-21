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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyMappingStatusRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface as StructureFamilyInterface;
use Akeneo\Test\Acceptance\Family\InMemoryFamilyRepository as InMemoryStructureFamilyRepository;

/**
 * In memory implementation of the repository of the attribute mapping read model "Family".
 *
 * This implementation is very basic, and has two flaws:
 * - it returns all the families when it should return only those related to subscribed products,
 * - the mapping status of the family is always "pending".
 *
 * This is not a problem for now, but will probably evolve while adding new acceptance tests.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class InMemoryFamilyMappingStatusRepository implements FamilyMappingStatusRepositoryInterface
{
    /** @var InMemoryStructureFamilyRepository */
    private $familyRepository;

    public function __construct(InMemoryStructureFamilyRepository $familyRepository)
    {
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch(int $page, int $limit, ?string $search): FamilyMappingStatusCollection
    {
        $families = $this->familyRepository->findAll();

        $families = $this->applyPagination($families, $page, $limit);
        $families = $this->applySearchFilter($families, $search);

        $familyCollection = new FamilyMappingStatusCollection();

        foreach ($families as $family) {
            $labels = [];
            foreach ($family->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $familyCollection->add($this->buildFamily($family));
        }

        return $familyCollection;
    }

    /**
     * @param StructureFamilyInterface $structureFamily
     *
     * @return FamilyMappingStatus
     */
    private function buildFamily(StructureFamilyInterface $structureFamily): FamilyMappingStatus
    {
        $labels = [];
        foreach ($structureFamily->getTranslations() as $translation) {
            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return new FamilyMappingStatus(
            new Family(new FamilyCode($structureFamily->getCode()), $labels),
            FamilyMappingStatus::MAPPING_PENDING
        );
    }

    /**
     * @param array $families
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    private function applyPagination(array $families, int $page, int $limit): array
    {
        $families = new \LimitIterator(
            new \ArrayIterator($families),
            $page,
            $limit
        );

        return iterator_to_array($families, false);
    }

    /**
     * @param array $families
     * @param null|string $search
     *
     * @return array
     */
    private function applySearchFilter(array $families, ?string $search): array
    {
        if (empty($search)) {
            return $families;
        }

        $families = array_filter($families, function (StructureFamilyInterface $family) use ($search) {
            if ($this->stringContains($family->getCode(), $search)
                || $this->stringContains($family->getLabel(), $search)
            ) {
                return true;
            }

            return false;
        });

        return $families;
    }

    /**
     * @param string $string
     * @param $search
     *
     * @return bool
     */
    private function stringContains(string $string, $search): bool
    {
        return false !== strpos($string, $search);
    }
}
