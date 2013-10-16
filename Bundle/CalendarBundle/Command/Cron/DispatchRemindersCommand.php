<?php

namespace Oro\Bundle\CalendarBundle\Command\Cron;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;

class DispatchRemindersCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * {@internaldoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * {@internaldoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:calendar-dispatch-reminders')
            ->setDescription('Dispatch calendar reminders');
    }

    /**
     * {@internaldoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        /** @var ImapEmailSynchronizer $synchronizer */
//        $synchronizer = $this->getContainer()->get('oro_imap.email_synchronizer');
//        $synchronizer->setLogger(new OutputLogger($output));
    }
}
