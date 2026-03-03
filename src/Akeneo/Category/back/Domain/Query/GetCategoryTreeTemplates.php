<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetCategoryTreeTemplates
{
    /**
     * @return TemplateUuid[]
     */
    public function __invoke(CategoryId $categoryTreeId): array;
}
