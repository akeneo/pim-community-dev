<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        $this
            ->runCommand('doctrine:schema:drop', array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create')
            ->runCommand('doctrine:fixtures:load', array('--no-interaction' => true))
            ->runCommand('oro:entity-config:init')
            ->runCommand('oro:entity-extend:init');

        return $this->complete();
    }
}
