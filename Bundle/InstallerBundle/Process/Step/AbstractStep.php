<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;

abstract class AbstractStep extends ControllerStep
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var StreamOutput
     */
    protected $output;

    /**
     * Execute Symfony2 command
     *
     * @param string $command Command name (for example, "cache:clear")
     * @param array  $params  [optional] Additional command parameters, like "--no-interaction", "--force" etc
     */
    protected function runCommand($command, $params = array())
    {
        $params = array_merge($params, array('command' => $command, '--no-debug' => true));

        $this->getApplication()->run(new ArrayInput($params), $this->output);
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        if (!$this->application) {
            $this->application = new Application($this->get('kernel'));
            $this->output      = new StreamOutput(
                fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bap_install.log', 'w')
            );

            $this->application->setAutoExit(false);
        }

        return $this->application;
    }
}
