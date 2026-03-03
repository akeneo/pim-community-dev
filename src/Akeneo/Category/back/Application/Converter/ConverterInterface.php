<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConverterInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function convert(array $data): array;
}
