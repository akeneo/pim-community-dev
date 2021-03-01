<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Command;

use Akeneo\Tool\Bundle\MessengerBundle\Query\PurgeDoctrineQueueQuery;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeMessengerCommand extends Command
{
    protected static $defaultName = 'akeneo:messenger:doctrine:purge-messages';

    private PurgeDoctrineQueueQuery $purgeDoctrineQueue;

    public function __construct(PurgeDoctrineQueueQuery $purgeDoctrineQueue)
    {
        parent::__construct();

        $this->purgeDoctrineQueue = $purgeDoctrineQueue;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription(
                'Purges the messenger SQL table in terms of the given retention time (default is 7200 seconds)'
            )
            ->addArgument(
                'table-name',
                InputArgument::REQUIRED,
                'Name of the messenger table to purge.'
            )
            ->addArgument(
                'queue-name',
                InputArgument::REQUIRED,
                'Name of the messenger queue to purge.'
            )
            ->addOption(
                'retention-time',
                null,
                InputOption::VALUE_OPTIONAL,
                'Deletes messages that are older than the given retention time in seconds.',
                7200
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $retentionTime = $input->getOption('retention-time');
        $tableName = $input->getArgument('table-name');
        $queueName = $input->getArgument('queue-name');
        $olderThan = $this->computeOlderThanDateTime((int) $retentionTime);

        try {
            $this->purgeDoctrineQueue->execute($tableName, $queueName, $olderThan);
        } catch (DBALException $dbalException) {
            $output->writeln($dbalException->getMessage());

            return -1;
        }

        return 0;
    }

    private function computeOlderThanDateTime(int $retentionTime): \DateTimeImmutable
    {
        $now = (int) (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('U');
        $retentionTimeAgo =  $now - $retentionTime;

        return \DateTimeImmutable::createFromFormat(
            'U',
            (string) $retentionTimeAgo,
            new \DateTimeZone('UTC')
        );
    }
}
