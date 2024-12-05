<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

final class GetBlobsOptions
{
    public function __construct(
        public readonly ?int $pageSize = null,
    ) {
    }
}
