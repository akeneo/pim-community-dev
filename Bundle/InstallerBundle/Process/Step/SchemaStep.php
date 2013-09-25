<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        $this
            ->runCommand('oro:entity-extend:clear')
            ->runCommand('doctrine:schema:drop', array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create')
            ->runCommand('doctrine:fixtures:load', array('--no-interaction' => true));

        return $this->complete();
    }
}
