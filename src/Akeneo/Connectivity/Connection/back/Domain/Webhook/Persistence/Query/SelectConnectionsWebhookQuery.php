<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

interface SelectConnectionsWebhookQuery
{
    /**
     * @return array<array{code: string, webhook: string}>
     */
    public function execute(): array;
}