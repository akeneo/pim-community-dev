<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetNextIdentifierQuery
{
    public function fromPrefix(Target $target, string $prefix): int;
}
