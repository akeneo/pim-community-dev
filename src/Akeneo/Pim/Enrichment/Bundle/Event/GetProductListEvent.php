<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Event;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductListEvent
{
    public function __construct(
        private array $data = []
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }
}
