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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Query\GetAttributeMappingStatusesFromFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilySearchableRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandler
{
    /** @var FamilySearchableRepositoryInterface */
    private $familyRepository;

    /** @var GetAttributeMappingStatusesFromFamilyCodesQueryInterface */
    private $getAttributeMappingStatusesFromFamilyCodesQuery;

    /**
     * @param FamilySearchableRepositoryInterface $familyRepository
     * @param GetAttributeMappingStatusesFromFamilyCodesQueryInterface $getAttributeMappingStatusesFromFamilyCodesQuery
     */
    public function __construct(
        FamilySearchableRepositoryInterface $familyRepository,
        GetAttributeMappingStatusesFromFamilyCodesQueryInterface $getAttributeMappingStatusesFromFamilyCodesQuery
    ) {
        $this->familyRepository = $familyRepository;
        $this->getAttributeMappingStatusesFromFamilyCodesQuery = $getAttributeMappingStatusesFromFamilyCodesQuery;
    }

    /**
     * @param SearchFamiliesQuery $getFamiliesQuery
     *
     * @return FamilyCollection
     */
    public function handle(SearchFamiliesQuery $getFamiliesQuery): FamilyCollection
    {
        $families = $this->familyRepository->findBySearch(
            $getFamiliesQuery->getPage(),
            $getFamiliesQuery->getLimit(),
            $getFamiliesQuery->getSearch(),
            $getFamiliesQuery->getFamilyIdentifiers()
        );

        $attributeMappingStatuses = $this->getAttributeMappingStatusesPerFamily($families);

        $familyCollection = new FamilyCollection();

        foreach ($families as $family) {
            $labels = [];
            foreach ($family->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $familyCollection->add(
                new Family(
                    $family->getCode(),
                    $labels,
                    $attributeMappingStatuses[$family->getCode()]
                )
            );
        }

        return $familyCollection;
    }

    /**
     * @param FamilyInterface[] $families
     *
     * @return array
     */
    private function getAttributeMappingStatusesPerFamily(array $families): array
    {
        $familyCodes = [];
        foreach ($families as $family) {
            $familyCodes[] = $family->getCode();
        }

        return $this->getAttributeMappingStatusesFromFamilyCodesQuery->execute($familyCodes);
    }
}
