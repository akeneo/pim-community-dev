<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface Value
{
    public function normalize(): array;

    public function getKey(): string;
}
