<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Domain\Model\Enrichment\Category;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetPositionInterface
{
    public function __invoke(Category $category): int;
}
