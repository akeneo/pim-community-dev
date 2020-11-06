<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

interface GetConnectionUserForFakeSubscription
{
    public function execute(): ?int;
}
