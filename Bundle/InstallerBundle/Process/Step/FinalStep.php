<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class FinalStep extends AbstractStep
{
    public function displayAction(ProcessContextInterface $context)
    {
        set_time_limit(120);

        $kernel  = $this->get('kernel');
        $env     = $kernel->getEnvironment();
        $console = escapeshellarg($this->getPhp()) . ' ' . escapeshellarg($kernel->getRootDir() . '/console');

        $params = $this->get('oro_installer.yaml_persister')->parse();

        // everything was fine - set %installed% flag to current date
        $params['system']['installed'] = date('c');

        $this->get('oro_installer.yaml_persister')->dump($params);

        $commands = array(
            new Process($console . ' oro:entity-config:init --env ' . $env),
            new Process($console . ' oro:entity-extend:init --env ' . $env),
            new Process($console . ' oro:entity-extend:update-config --env ' . $env),
            new Process($console . ' doctrine:schema:update --force --env ' . $env),
            new Process($console . ' oro:search:create-index --env ' . $env),
            new Process($console . ' oro:navigation:init --env ' . $env),
            new Process($console . ' assets:install ./ --env ' . $env),
            new Process($console . ' assetic:dump --env ' . $env),
            new Process($console . ' oro:assetic:dump --env ' . $env),
            new Process($console . ' oro:translation:dump --env ' . $env),
        );

        foreach ($commands as $command) {
            /** @var $command Process */
            $command->run();

            while ($command->isRunning()) {
                /** wait for previous process */
            }
        }

        $this->complete();

        return $this->render('OroInstallerBundle:Process/Step:final.html.twig');
    }

    protected function getPhp()
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }
}
