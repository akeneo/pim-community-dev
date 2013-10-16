<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        switch ($this->getRequest()->query->get('action')) {
            case 'cache':
                return $this->handleAjaxAction('cache:clear');
            case 'drop':
                return $this->handleAjaxAction('doctrine:schema:drop', array('--force' => true, '--full-database' => true));
            case 'clear':
                return $this->handleAjaxAction('oro:entity-extend:clear');
            case 'create':
                return $this->handleAjaxAction('doctrine:schema:create');
            case 'fixtures':
                return $this->handleAjaxAction('doctrine:fixtures:load', array('--no-interaction' => true));
            case 'init-config':
                return $this->handleAjaxAction('oro:entity-config:init');
            case 'init-extend':
                return $this->handleAjaxAction('oro:entity-extend:init');
            case 'update-config':
                return $this->handleAjaxAction('oro:entity-extend:update-config');
        }

        return $this->render('OroInstallerBundle:Process/Step:schema.html.twig');
    }
}
