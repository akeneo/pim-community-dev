<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Storage;

interface ProfileRepositoryInterface
{
    public function findAll(): array;
}
