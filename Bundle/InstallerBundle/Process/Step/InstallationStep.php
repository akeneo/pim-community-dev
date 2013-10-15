<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class InstallationStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        switch ($this->getRequest()->query->get('action')) {
            case 'config':
                return $this->handleSchemeAction('oro:entity-extend:update-config');

            case 'schema':
                return $this->handleSchemeAction('doctrine:schema:update', array('--force' => true));

            case 'search':
                return $this->handleSchemeAction('oro:search:create-index');

            case 'navigation':
                return $this->handleSchemeAction('oro:navigation:init');

            case 'assets':
                return $this->handleSchemeAction('assets:install', array('target' => './'));

            case 'assetic':
                return $this->handleSchemeAction('assetic:dump');

            case 'assetic-oro':
                return $this->handleSchemeAction('oro:assetic:dump');

            case 'translation':
                return $this->handleSchemeAction('oro:translation:dump');

            case 'cache':
                return $this->handleSchemeAction('cache:clear', array('--no-warmup' => true));
        }

        return $this->render('OroInstallerBundle:Process/Step:installation.html.twig');
    }
}
