<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Requests;

enum BlockType: string
{
    case COMMITTED = "Committed";
    case UNCOMMITTED = "Uncommitted";
    case LATEST = "Latest";
}
