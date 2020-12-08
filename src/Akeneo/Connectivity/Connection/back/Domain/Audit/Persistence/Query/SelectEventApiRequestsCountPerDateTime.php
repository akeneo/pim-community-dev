<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query;


interface SelectEventApiRequestsCountPerDateTime
{
    public function execute(\DateTimeImmutable $dateTime): int;
}
