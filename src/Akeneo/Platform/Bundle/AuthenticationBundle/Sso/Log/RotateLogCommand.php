<?php
declare(strict_types=1);

/*
 * this file is part of the akeneo pim enterprise edition.
 *
 * (c) 2014 akeneo sas (http://www.akeneo.com)
 *
 * for the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command rotates log entries from the SSO log table.
 */
final class RotateLogCommand extends Command
{
    protected static $defaultName = 'pimee:sso:rotate-log';

    /** @var Connection */
    private $connection;

    /** @static string */
    const TABLE_NAME = 'pimee_sso_log';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription(
                'Rotate SSO Logs in the SSO logs table.'
            )
            ->addArgument(
                'max-days',
                InputArgument::REQUIRED,
                'The number of days of logs to keep'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $maxDays = (int) $input->getArgument('max-days');

        if ($maxDays < 0) {
            throw new \LogicException(
                sprintf('Number of days must be greater or equal to 0. %s given.', $maxDays)
            );
        }

        $output->writeln(sprintf("<info>Rotating SSO logs, keeping %s days...</info>", $maxDays));
        $expirationDate = new \DateTime(sprintf("%s days ago", $maxDays));

        $this->connection->executeQuery(
            sprintf('DELETE FROM %s WHERE time < :expirationTime', self::TABLE_NAME),
            [
                'expirationTime' => $this->connection->convertToDatabaseValue($expirationDate, 'datetime')
            ]
        );
        $output->writeln(sprintf("<info>SSO logs rotation done.</info>", $maxDays));
    }
}
