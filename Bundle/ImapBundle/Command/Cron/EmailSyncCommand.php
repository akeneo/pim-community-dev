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
     * {@internaldoc}
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
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
        $synchronizer->sync(5, 0);
    }
}
