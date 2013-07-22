<?php

namespace Oro\Bundle\NotificationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;

/**
 * Class SpoolSendCommand
 * Console command implementation
 *
 * @package Oro\Bundle\NotificationBundle\Command
 */
class SpoolSendCommand extends SendEmailCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('oro:spool:send');
        $this->setHelp(str_replace('swiftmailer', 'oro', $this->getHelp()));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailer = $this->getContainer()->get('oro_notification.mailer');
        $this->getContainer()->set('mailer', $mailer);

        var_dump($this->getContainer()->get('mailer'));die;

        parent::execute($input, $output);
    }
}
