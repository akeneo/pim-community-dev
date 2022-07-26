<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetDirectChildrenCategoryCodesInterface
{
    /**
     * @return array<string, array{code: string, row_num: int}>
     */
    public function execute(int $categoryId): array;
}
