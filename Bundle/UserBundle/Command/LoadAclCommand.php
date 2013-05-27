<?php

namespace Oro\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class LoadAclCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:acl:load');
        $this->setDescription('Load ACL resources from annotations and config files to db');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Load ACL resources from annotations and config files to db');
        $this->getContainer()->get('oro_user.acl_manager')->synchronizeAclResources();
        $output->writeln('Completed');

        //clear cache
        $this->getApplication()->find('cache:clear')->run($input, $output);
    }
}
