<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

interface FamilyRepositoryInterface
{
    /**
     * Check if a family exist from its code.
     *
     * @param FamilyCode $familyCode
     *
     * @return bool
     */
    public function exist(FamilyCode $familyCode): bool;

    /**
     * @param FamilyCode $familyCode
     *
     * @return Family
     */
    public function findOneByIdentifier(FamilyCode $familyCode): ?Family;
}
