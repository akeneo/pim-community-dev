<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface Requirement
{
    /**
     * @param array<mixed> $data
     */
    public function check(array $data): void;
}
