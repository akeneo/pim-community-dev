<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface RequirementChecker
{
    /**
     * @param array<mixed> $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void;
}
