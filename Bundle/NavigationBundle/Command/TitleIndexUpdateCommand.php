<?php

namespace Oro\Bundle\NavigationBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class TitleIndexLoadCommand
 * Console command implementation
 *
 * @package Oro\Bundle\NavigationBundle\Command
 */
class TitleIndexUpdateCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:navigation:init');
        $this->setDescription('Load "Title Templates" from annotations and config files to db');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());

        $this->update($this->getContainer()->get('router')->getRouteCollection()->all());

        $output->writeln('Completed');
    }

    /**
     * Update titles index
     *
     * @param array $routes
     */
    private function update($routes)
    {
        $toUpdate = array();
        foreach ($routes as $name => $route) {
            /** @var $route \Symfony\Component\Routing\Route  */

            $requirements = $route->getRequirements();

            if ($this->checkMethod($requirements) && $route->getDefault('_controller') != 'assetic.controller:render') {
                $toUpdate[$name] = $route;
            }
        }

        $this->getContainer()->get('oro_navigation.title_service')->update($toUpdate);
    }

    /**
     * Check if allowed GET method
     *
     * @param  array $requirements
     * @return bool
     */
    private function checkMethod($requirements)
    {
        $method = isset($requirements['_method']) ?  $requirements['_method'] : 'ANY';

        $allowed = array('GET', 'ANY');
        $method = !is_array($method) ? explode('|', $method) : $method;

        $result = (boolean) count(array_intersect($allowed, $method));

        return $result;
    }
}
