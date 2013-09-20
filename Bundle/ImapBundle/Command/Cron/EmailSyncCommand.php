<?php

namespace Oro\Bundle\ImapBundle\Command\Cron;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\ImapBundle\Sync\ImapEmailSynchronizer;
use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;

class EmailSyncCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * The maximum number of synchronization tasks to be executed
     */
    const MAX_TASKS = -1;

    /**
     * The maximum number of synchronization tasks running in the same time
     */
    const MAX_CONCURRENT_TASKS = 5;

    /**
     * The time interval (in minutes) a synchronization for
     * the same email origin can be executed
     */
    const MIN_EXEC_PERIOD_IN_MIN = 0;

    /**
     * The maximum time frame (in minutes) this synchronization jobs can spend at one run
     */
    const MAX_EXEC_TIMEOUT_IN_MIN = 15;

    /**
     * {@internaldoc}
     */
    public function getDefaultDefinition()
    {
        return '*/30 * * * *';
    }

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:imap-sync')
            ->setDescription('Synchronization emails via IMAP');
    }

    /**
     * {@internaldoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ImapEmailSynchronizer $synchronizer */
        $synchronizer = $this->getContainer()->get('oro_imap.email_synchronizer');
        $synchronizer->setLogger(new OutputLogger($output));
        $synchronizer->sync(
            self::MAX_CONCURRENT_TASKS,
            self::MIN_EXEC_PERIOD_IN_MIN,
            self::MAX_EXEC_TIMEOUT_IN_MIN,
            self::MAX_TASKS
        );
    }
}
