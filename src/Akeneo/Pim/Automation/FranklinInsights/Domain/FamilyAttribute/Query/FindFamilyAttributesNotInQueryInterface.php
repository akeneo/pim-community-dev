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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface FindFamilyAttributesNotInQueryInterface
{
    /**
     * Returns family attributes that are not in the given array of attribute codes. It could help you to know what
     * are the attributes removed from the family for example.
     *
     * @param string $familyCode
     * @param array $attributeCodes
     *
     * @return array array of attribute codes or empty array
     */
    public function findFamilyAttributesNotIn(string $familyCode, array $attributeCodes): array;
}
