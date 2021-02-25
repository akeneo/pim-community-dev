<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Cli;

use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\PurgeEventsApiLogsQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeEventsApiLogsCommand extends Command
{
    protected static $defaultName = 'akeneo:connectivity-connection:purge-events-api-logs';

    /** @var PurgeConnectionErrorsQuery */
    private $purgeLogsQuery;

    public function __construct(
        PurgeEventsApiLogsQuery $purgeLogsQuery
    ) {
        parent::__construct();
        $this->purgeLogsQuery = $purgeLogsQuery;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->purgeLogsQuery->execute();

        return 0;
    }
}
