<?php

namespace Akeneo\Pim\Permission\Bundle\Application\Query;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
interface SaveCategoryProductPermissionsInterface
{
    /**
     * @param array<string, array<int>> $permissions
     */
    public function __invoke(int $categoryId, array $permissions): void;
}
