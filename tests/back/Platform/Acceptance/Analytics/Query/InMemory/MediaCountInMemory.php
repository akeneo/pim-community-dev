<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\Analytics\Query\InMemory;

use Akeneo\Tool\Component\Analytics\MediaCountQuery;

class MediaCountInMemory implements MediaCountQuery
{
    public function countFiles(): int
    {
        return 0;
    }

    public function countImages(): int
    {
        return 1;
    }
}
