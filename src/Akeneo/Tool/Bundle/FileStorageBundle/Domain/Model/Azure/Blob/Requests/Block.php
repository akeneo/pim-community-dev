<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests;

final class Block
{
    public function __construct(
        public string $id,
        public BlockType $type,
    ) {
    }
}
