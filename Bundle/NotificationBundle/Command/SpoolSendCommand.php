<?php

namespace Oro\Bundle\NotificationBundle\Command;

use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        parent::execute($input, $output);
    }



}
