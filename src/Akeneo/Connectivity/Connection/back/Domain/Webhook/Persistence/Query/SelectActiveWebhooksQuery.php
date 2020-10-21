<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ActiveWebhook;

interface SelectActiveWebhooksQuery
{
    /**
     * @return ActiveWebhook[]
     */
    public function execute(): array;
}
