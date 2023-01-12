<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClearAccessTokenCommand extends Command
{
    protected static $defaultName = 'akeneo:test:delete-tokens';

    private const DEFAULT_BATCH_SIZE = 500;

    public function __construct(
        private readonly Connection $connection
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Clears invalid access token')
            ->addOption(
                name: 'batch',
                mode: InputOption::VALUE_OPTIONAL,
                default: self::DEFAULT_BATCH_SIZE
            )
            ->addOption(
                name: 'max',
                mode: InputOption::VALUE_OPTIONAL,
                default: 0
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $max = (int) $input->getOption('max');
        $batchSize = (int) $input->getOption('batch');
        $stopwatch = new Stopwatch();
        $stopwatch->start('accesstoken');

        $sqlQuery = <<<SQL
            DELETE FROM pim_api_access_token
            WHERE expires_at < now()
            LIMIT :row_count;
            SQL;

        $statement = $this->connection->prepare($sqlQuery);
        $statement->bindValue('row_count', $batchSize, ParameterType::INTEGER);

        $section = $output->section();
        $rowsDeleted  = 0;
        $section->writeln("DELETED $rowsDeleted rows");
        do {
            $affectedRows = $statement->executeStatement();
            $stopwatch->lap('accesstoken');

            $rowsDeleted += $affectedRows;
            $section->overwrite("DELETED $rowsDeleted rows");

        } while ($affectedRows >= $batchSize && ($max <= 0  || $rowsDeleted < $max));

        $event = $stopwatch->stop('accesstoken');
        $output->writeln($event);
        $output->writeln('Detailed report of first 10 batches:');
        $periods = $event->getPeriods();
        for ($i = 0; $i < 10; $i++) {
            $output->writeln($periods[$i] ?? '');
        }

        return Command::SUCCESS;
    }
}
