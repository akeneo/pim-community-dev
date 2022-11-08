<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\CategoryId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetCategoryTemplateByCategoryTree
{
    public function __invoke(CategoryId $categoryTreeId): ?Template;
}
