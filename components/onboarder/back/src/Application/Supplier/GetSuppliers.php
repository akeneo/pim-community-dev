<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

final class GetSuppliers
{
    public function __construct(public int $page = 1, public string $search = '')
    {
    }
}
