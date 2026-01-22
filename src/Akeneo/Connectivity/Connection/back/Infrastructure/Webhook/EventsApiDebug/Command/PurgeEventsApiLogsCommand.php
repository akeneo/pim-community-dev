<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Command;

use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\PurgeEventsApiErrorLogsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\PurgeEventsApiSuccessLogsQuery;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Psr\Log\LoggerInterface;
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
    /**
     * @var string
     */
    protected static $defaultName = 'akeneo:connectivity-connection:purge-events-api-logs';

    public function __construct(
        private PurgeEventsApiSuccessLogsQuery $purgeSuccessLogsQuery,
        private PurgeEventsApiErrorLogsQuery $purgeErrorLogsQuery,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->purgeSuccessLogsQuery->execute();
            $this->purgeErrorLogsQuery->execute((new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
                ->sub(new \DateInterval('PT72H')));
        } catch (ClientResponseException $ex) {
            if ($ex->getCode() === 404) {
                $this->logger->warning('Elasticsearch index not found', ['exception' => $ex]);
                return Command::FAILURE;
            }
            throw $ex;
        } catch (NoNodeAvailableException $ex) {
            $this->logger->warning('Elasticsearch is unavailable', ['exception' => $ex]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
