<?php

namespace Akeneo\Tool\Component\StorageUtils\Repository;

interface RemovableObjectRepositoryInterface
{
    public function remove(string $identifier): void;
}
