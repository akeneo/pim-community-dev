<?php

declare(strict_types=1);

namespace Akeneo\Category\Api;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryChildrenCodesPerTreeInterface
{
    /**
     * For each category tree, return children codes of selected categories.
     *
     * @param array<string> $categoryCodes
     *
     * @return array<string, string[]>
     */
    public function executeWithChildren(array $categoryCodes): array;

    /**
     * For each category tree, return existing category codes of selected categories.
     *
     * @param array<string> $categoryCodes
     *
     * @return array<string, string[]>
     */
    public function executeWithoutChildren(array $categoryCodes): array;
}
