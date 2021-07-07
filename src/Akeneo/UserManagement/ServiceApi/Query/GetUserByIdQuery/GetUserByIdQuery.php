<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ServiceApi\Query\GetUserByIdQuery;

final class GetUserByIdQuery
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
