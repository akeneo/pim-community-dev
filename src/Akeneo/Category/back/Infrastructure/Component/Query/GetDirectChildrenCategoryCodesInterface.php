<?php
declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetDirectChildrenCategoryCodesInterface
{
    /**
     * @return array<string, int>
     */
    public function execute(int $categoryId): array;
}
