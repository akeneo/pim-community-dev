<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Query;

use Akeneo\Connectivity\Connection\Application\Webhook\Query\GetAConnectionWebhookQuery as GetAConnectionWebhookQueryCqrs;
use Akeneo\Connectivity\Connection\Domain\Webhook\Model\Read\ConnectionWebhook;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\GetAConnectionWebhookQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAConnectionWebhookHandler
{
    /** @var GetAConnectionWebhookQuery */
    private $dbQuery;

    public function __construct(GetAConnectionWebhookQuery $dbQuery)
    {
        $this->dbQuery = $dbQuery;
    }

    public function handle(GetAConnectionWebhookQueryCqrs $cqrsQuery): ?ConnectionWebhook
    {
        return $this->dbQuery->execute($cqrsQuery->code());
    }
}
