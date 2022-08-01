<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Converter\InternalAPI;

use Akeneo\Category\Application\Converter\ConverterInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalAPIToStdStub implements ConverterInterface
{
    public function convert(array $data): array
    {
        return $data;
    }
}
