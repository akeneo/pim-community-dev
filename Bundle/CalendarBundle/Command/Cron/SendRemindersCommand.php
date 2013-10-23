<?php

namespace Oro\Bundle\CalendarBundle\Command\Cron;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\CronBundle\Command\Logger\OutputLogger;
use Oro\Bundle\CalendarBundle\Notification\RemindersSender;

class SendRemindersCommand extends ContainerAwareCommand implements CronCommandInterface
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
            ->setName('oro:cron:send-calendar-reminders')
            ->setDescription('Send calendar reminders');
    }

    /**
     * {@internaldoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var RemindersSender $sender */
        $sender = $this->getContainer()->get('oro_calendar.reminders_sender');
        $sender->setLogger(new OutputLogger($output));
        $sender->send();
    }
}
