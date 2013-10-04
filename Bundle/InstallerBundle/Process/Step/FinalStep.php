<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            return $this->redirect($this->getRequest()->getBasePath() . '/install.php');
        }

        set_time_limit(600);

        $params = $this->get('oro_installer.yaml_persister')->parse();

        // everything was fine - set %installed% flag to current date
        $params['system']['installed']        = date('c');
        $params['session']['session_handler'] = 'session.handler.native_file';

        $this->get('oro_installer.yaml_persister')->dump($params);

        $this
            ->runCommand('doctrine:schema:update', array('--force' => true))
            ->runCommand('oro:search:create-index')
            ->runCommand('oro:navigation:init')
            ->runCommand('assets:install', array('target' => './'))
            ->runCommand('assetic:dump')
            ->runCommand('oro:assetic:dump')
            ->runCommand('oro:translation:dump')
            ->runCommand('oro:requirejs:config')
            ->runCommand('oro:requirejs:build')
            ->runCommand('cache:clear', array('--no-warmup' => true));

        $this->complete();

        return $this->render('OroInstallerBundle:Process/Step:final.html.twig');
    }
}
