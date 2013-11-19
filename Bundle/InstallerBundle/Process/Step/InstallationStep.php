<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Oro\Bundle\InstallerBundle\InstallerEvents;

class InstallationStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(900);

        switch ($this->getRequest()->query->get('action')) {
            case 'fixtures':
                return $this->handleAjaxAction('oro:demo:fixtures:load');
            case 'search':
                return $this->handleAjaxAction('oro:search:create-index');
            case 'navigation':
                return $this->handleAjaxAction('oro:navigation:init');
            case 'localization':
                return $this->handleAjaxAction('oro:localization:dump');
            case 'assets':
                return $this->handleAjaxAction('assets:install', array('target' => './'));
            case 'assetic':
                return $this->handleAjaxAction('assetic:dump');
            case 'assetic-oro':
                return $this->handleAjaxAction('oro:assetic:dump');
            case 'translation':
                return $this->handleAjaxAction('oro:translation:dump');
            case 'requirejs':
                return $this->handleAjaxAction('oro:requirejs:build');
            case 'finish':
                $this->get('event_dispatcher')->dispatch(InstallerEvents::FINISH);
                // everything was fine - update installed flag in parameters.yml
                $dumper = $this->get('oro_installer.yaml_persister');
                $params = $dumper->parse();
                $params['system']['installed'] = date('c');
                $dumper->dump($params);
                // launch 'cache:clear' to set installed flag in DI container
                // suppress warning: ini_set(): A session is active. You cannot change the session
                // module's ini settings at this time
                error_reporting(E_ALL ^ E_WARNING);
                return $this->handleAjaxAction('cache:clear');
        }

        return $this->render(
            'OroInstallerBundle:Process/Step:installation.html.twig',
            array(
                'loadFixtures' => $context->getStorage()->get('loadFixtures'),
            )
        );
    }
}
