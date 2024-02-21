<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\BulkInsertEventCountsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\ExtractConnectionsProductEventCountQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataSourceProductEventCountHandlerSpec extends ObjectBehavior
{
    public function let(
        ExtractConnectionsProductEventCountQueryInterface $extractConnectionsEventCountQuery,
        BulkInsertEventCountsQueryInterface $bulkInsertEventCountsQuery,
    ): void {
        $this->beConstructedWith($extractConnectionsEventCountQuery, $bulkInsertEventCountsQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(UpdateDataSourceProductEventCountHandler::class);
    }
}
