<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;

class SchemaStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(600);

        $this
            ->runCommand('doctrine:schema:drop', array('--force' => true, '--full-database' => true))
            ->runCommand('oro:entity-extend:clear')
            ->runCommand('doctrine:schema:create')
            ->runCommand('doctrine:fixtures:load', array('--no-interaction' => true))
            ->runCommand('oro:entity-config:init');

        /**
         * @todo Refactor isolate process execution
         */
        $finder  = new PhpExecutableFinder();
        $kernel  = $this->get('kernel');
        $console = escapeshellarg($finder->find()) . ' ' . escapeshellarg($kernel->getRootDir() . '/console');
        $process = new Process($console . ' oro:entity-extend:init --env=' . $kernel->getEnvironment());

        $process->run();

        return $this->complete();
    }
}
