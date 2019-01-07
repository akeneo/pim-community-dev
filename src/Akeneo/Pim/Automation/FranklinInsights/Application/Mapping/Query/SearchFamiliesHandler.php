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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SearchFamiliesHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     */
    public function __construct(FamilyRepositoryInterface $familyRepository)
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
        return $this->familyRepository->findBySearch(
            $getFamiliesQuery->getPage(),
            $getFamiliesQuery->getLimit(),
            $getFamiliesQuery->getSearch()
        );
    }
}
