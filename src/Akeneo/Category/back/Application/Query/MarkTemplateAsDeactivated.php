<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MarkTemplateAsDeactivated
{
    public function execute(string $uuid): void;
}
