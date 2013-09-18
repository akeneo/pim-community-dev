<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(120);

        $params = $this->get('oro_installer.yaml_persister')->parse();

        // everything was fine - set %installed% flag to current date
        $params['system']['installed']        = date('c');
        $params['session']['session_handler'] = 'session.handler.pdo';

        $this->get('oro_installer.yaml_persister')->dump($params);

        $this->runCommand('oro:acl:load');
        $this->runCommand('oro:navigation:init');
        $this->runCommand('oro:entity-config:update');
        $this->runCommand('oro:entity-extend:create');
        $this->runCommand('cache:clear');
        $this->runCommand('doctrine:schema:update', array('--force' => true));
        $this->runCommand('oro:search:create-index');
        $this->runCommand('assets:install');
        $this->runCommand('assetic:dump');
        $this->runCommand('oro:assetic:dump');

        $this->complete();

        return $this->render('OroInstallerBundle:Process/Step:final.html.twig');
    }
}
