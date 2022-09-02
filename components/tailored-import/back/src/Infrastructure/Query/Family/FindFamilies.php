<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query\Family;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyCodes;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQueryPagination;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuerySearch;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;
use Akeneo\Platform\TailoredImport\Domain\Query\Family\FindFamiliesInterface;
use Akeneo\Platform\TailoredImport\Domain\Query\Family\FindFamiliesResult;

class FindFamilies implements FindFamiliesInterface
{
    public function __construct(
        private FindFamiliesWithLabels $findFamiliesWithLabels,
        private CountFamilyCodes $countFamilyCodes,
    ) {
    }

    public function execute(
        string $localeCode,
        int $limit,
        int $page = 1,
        string $search = null,
        ?array $includeCodes = null,
        ?array $excludeCodes = null,
    ): FindFamiliesResult {
        $searchQuery = new FamilyQuerySearch($search, $localeCode);
        $searchPagination = new FamilyQueryPagination($page, $limit);

        $query = new FamilyQuery(
            $searchQuery,
            $searchPagination,
            $includeCodes,
            $excludeCodes,
        );

        $families = $this->findFamiliesWithLabels->fromQuery($query);
        $matchesCount = $this->countFamilyCodes->fromQuery($query);

        return new FindFamiliesResult($families, $matchesCount);
    }
}
