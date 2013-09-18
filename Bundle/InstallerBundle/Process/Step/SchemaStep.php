<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(120);

        $this->runCommand('doctrine:schema:create');
        $this->runCommand('doctrine:fixtures:load', array('--no-interaction' => true));

        return $this->complete();
    }
}
