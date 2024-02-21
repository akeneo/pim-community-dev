<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserIntentsFromStandardFormat
{
    /**
     * @param array<string, mixed> $standardFormat
     */
    public function __construct(private array $standardFormat)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function standardFormat(): array
    {
        return $this->standardFormat;
    }
}
