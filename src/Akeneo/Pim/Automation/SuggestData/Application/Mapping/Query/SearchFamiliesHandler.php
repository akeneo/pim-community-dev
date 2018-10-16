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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\FamilySearchableRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandler
{
    /** @var FamilySearchableRepositoryInterface */
    private $familyRepository;

    /**
     * @param FamilySearchableRepositoryInterface $familyRepository
     */
    public function __construct(FamilySearchableRepositoryInterface $familyRepository)
    {
        $this->familyRepository = $familyRepository;
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

        $familyCollection = new FamilyCollection();

        foreach ($families as $family) {
            $labels = [];
            foreach ($family->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $familyCollection->add(new Family($family->getCode(), $labels));
        }

        return $familyCollection;
    }
}
