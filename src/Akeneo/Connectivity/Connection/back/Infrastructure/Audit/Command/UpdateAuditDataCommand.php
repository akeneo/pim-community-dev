<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Command;

use Akeneo\Connectivity\Connection\Infrastructure\Audit\UpdateAuditData;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateAuditDataCommand extends Command
{
    private const MYSQL_IS_UNAVAILABLE_ERROR_CODE = 2002;
    private const TABLE_NOT_FOUND_ERROR_CODE = 42;

    protected static $defaultName = 'akeneo:connectivity-audit:update-data';

    public function __construct(
        private UpdateAuditData $updateAuditData,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->updateAuditData->execute();
        } catch (ConnectionException $exception) {
            if ($exception->getPrevious()?->getCode() === self::MYSQL_IS_UNAVAILABLE_ERROR_CODE) {
                $this->logger->warning('Mysql is unavailable', ['exception' => $exception]);

                return Command::FAILURE;
            }

            throw $exception;
        } catch (TableNotFoundException $exception) {
            if ($exception->getPrevious()?->getCode() === self::TABLE_NOT_FOUND_ERROR_CODE) {
                $this->logger->warning('Table not found', ['exception' => $exception]);

                return Command::FAILURE;
            }

            throw $exception;
        }

        return Command::SUCCESS;
    }
}
