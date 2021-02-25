<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeEventsApiErrorLogsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeEventsApiSuccessLogsQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiLogsCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:purge-events-api-logs';

    private PurgeEventsApiSuccessLogsQuery $purgeSuccessLogsQuery;
    private PurgeEventsApiErrorLogsQuery $purgeErrorLogsQuery;

    public function __construct(
        PurgeEventsApiSuccessLogsQuery $purgeSuccessLogsQuery,
        PurgeEventsApiErrorLogsQuery $purgeErrorLogsQuery
    ) {
        parent::__construct();
        $this->purgeSuccessLogsQuery = $purgeSuccessLogsQuery;
        $this->purgeErrorLogsQuery = $purgeErrorLogsQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->purgeSuccessLogsQuery->execute();
        $this->purgeErrorLogsQuery->execute();

        return 0;
    }
}
