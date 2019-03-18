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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface FamilyMappingStatusRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $page
     * @param null|string $search
     *
     * @return FamilyMappingStatusCollection
     */
    public function findBySearch(int $page, int $limit, ?string $search): FamilyMappingStatusCollection;
}
