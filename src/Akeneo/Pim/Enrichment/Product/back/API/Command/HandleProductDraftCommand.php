<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command;

use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class HandleProductDraftCommand
{
    public function __construct(
        private readonly UuidInterface $uuid,
        private readonly array $data
    ) {
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }
}
