<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        $params = $this->get('oro_installer.yaml_persister')->parse();

        // everything was fine - set %installed% flag to current date
        $params['system']['installed'] = date('c');

        $this->get('oro_installer.yaml_persister')->dump($params);

        $this
            ->runCommand('oro:entity-config:init')
            ->runCommand('oro:entity-extend:init')
            ->runCommand('oro:entity-extend:update-config')
            ->runCommand('doctrine:schema:update', array('--force' => true))
            ->runCommand('oro:search:create-index')
            ->runCommand('oro:navigation:init')
            ->runCommand('assets:install', array('target' => './'))
            ->runCommand('assetic:dump')
            ->runCommand('oro:assetic:dump')
            ->runCommand('oro:translation:dump');

        $this->complete();

        return $this->render('OroInstallerBundle:Process/Step:final.html.twig');
    }
}
