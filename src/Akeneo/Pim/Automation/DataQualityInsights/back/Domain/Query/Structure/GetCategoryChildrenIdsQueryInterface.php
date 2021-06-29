<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryChildrenIdsQueryInterface
{
    /**
     * @return int[] List of all the children ids of a category (and its own id)
     */
    public function execute(CategoryCode $categoryCode): array;
}
