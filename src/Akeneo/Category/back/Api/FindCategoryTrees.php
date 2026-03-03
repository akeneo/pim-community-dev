<?php

declare(strict_types=1);

namespace Akeneo\Category\Api;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindCategoryTrees
{
    /**
     * @return CategoryTree[]
     */
    public function execute(): array;
}
