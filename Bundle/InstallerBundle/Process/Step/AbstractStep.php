<?php

namespace Oro\Bundle\InstallerBundle\Process\Step;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *
     * @param  string $command
     * @param  array  $params
     * @return mixed
     */
    protected function handleAjaxAction($command, $params = array())
    {
        $this->runCommand($command, $params);

        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse(array('result' => true))
            : $this->redirect(
                $this->generateUrl(
                    'sylius_flow_display',
                    array(
                        'scenarioAlias' => 'oro_installer',
                        'stepName'      => $this->getName(),
                    )
                )
            );
    }

    /**
     * Execute Symfony2 command
     *
     * @param  string            $command Command name (for example, "cache:clear")
     * @param  array             $params  [optional] Additional command parameters, like "--force" etc
     * @return AbstractStep
     * @throws \Exception
     * @throws \RuntimeException
     */
    protected function runCommand($command, $params = array())
    {
        $output = $this->getOutput();
        $params = array_merge(
            array(
                'command'    => $command,
                '--no-debug' => true,
            ),
            $params
        );

        $output->writeln('');
        $output->writeln(sprintf('[%s] Launching "%s" command', date('Y-m-d H:i:s'), $command));

        $mem  = (int) memory_get_usage() / (1024 * 1024);
        $time = time();
        $code = $this->getApplication()->run(new ArrayInput($params), $output);

        $output->writeln('');
        $output->writeln(
            sprintf(
                'Command "%s" executed in %u second(s), memory usage: %.2fMb',
                $command,
                time() - $time,
                (int) memory_get_usage() / (1024 * 1024) - $mem
            )
        );
        $output->writeln('');

        // check for any error
        if ($code) {
            throw new \RuntimeException(
                sprintf('There was an error while running "%s" command. Exit code: %u.', $command, $code)
            );
        }

        return $this;
    }

    /**
     * @return Application
     */
    protected function getApplication()
    {
        if (!$this->application) {
            $this->application = new Application($this->get('kernel'));

            $this->application->setAutoExit(false);
        }

        return $this->application;
    }

    /**
     * @return StreamOutput
     */
    protected function getOutput()
    {
        if (!$this->output) {
            $this->output = new StreamOutput(
                fopen($this->container->getParameter('kernel.logs_dir') . DIRECTORY_SEPARATOR . 'oro_install.log', 'a+')
            );
        }

        return $this->output;
    }
}
