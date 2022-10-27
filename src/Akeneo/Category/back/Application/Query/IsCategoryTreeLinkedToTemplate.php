<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface IsCategoryTreeLinkedToTemplate
{
    public function __invoke(CategoryId $categoryTreeId): bool;
}
