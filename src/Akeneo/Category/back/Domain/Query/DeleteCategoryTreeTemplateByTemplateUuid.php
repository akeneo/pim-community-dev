<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface DeleteCategoryTreeTemplateByTemplateUuid
{
    public function __invoke(TemplateUuid $templateUuid): void;
}
