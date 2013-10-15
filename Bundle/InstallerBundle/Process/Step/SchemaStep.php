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
                return $this->handleSchemeAction('cache:clear');

            case 'drop':
                return $this->handleSchemeAction('doctrine:schema:drop', array('--force' => true, '--full-database' => true));

            case 'clear':
                return $this->handleSchemeAction('oro:entity-extend:clear');

            case 'create':
                return $this->handleSchemeAction('doctrine:schema:create');

            case 'fixtures':
                return $this->handleSchemeAction('doctrine:fixtures:load', array('--no-interaction' => true));

            case 'init-config':
                return $this->handleSchemeAction('oro:entity-config:init');

            case 'init-extend':
                return $this->handleSchemeAction('oro:entity-extend:init');
        }

        return $this->render('OroInstallerBundle:Process/Step:schema.html.twig');
    }
}
