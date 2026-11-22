<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Command;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\PurgeConnectionErrorsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\SelectAllAuditableConnectionCodeQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(name: 'akeneo:connectivity-connection:purge-error', description: 'Purge connection errors over 100 and older than a week')]
class PurgeConnectionErrorsCommand extends Command
{
    /**
     * @var string
     */

    public function __construct(
        private SelectAllAuditableConnectionCodeQuery $selectAllAuditableConnectionCodes,
        private PurgeConnectionErrorsQuery $purgeErrors,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Start purge connection error');

        $codes = $this->selectAllAuditableConnectionCodes->execute();
        $this->purgeErrors->execute($codes);

        $this->logger->info('End purge connection error');

        return Command::SUCCESS;
    }
}
