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

namespace PimEnterprise\Component\ProductAsset\Persistence\Query\Sql;

/**
 * It finds families codes where given attributes codes are required.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface FindFamiliesCodesWhereAttributesAreRequiredInterface
{
    /**
     * @param string[] $attributeCodes
     *
     * @return string[]
     */
    public function find(array $attributeCodes): array;
}
