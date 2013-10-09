<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class SystemAwareResolver implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param string $datagridName
     * @param array $datagridDefinition
     * @return array
     */
    public function resolve($datagridName, $datagridDefinition)
    {
        foreach ($datagridDefinition as $key => $val) {
            if (is_array($val)) {
                $datagridDefinition[$key] = $this->resolve($datagridName, $val);
                continue;
            }

            switch (true) {
                // static call class:method or class::const
                case preg_match('#%([\w\._]+)%::([\w\._]+)#', $val, $match):
                    // with class as param
                    $class = $this->container->getParameter($match[1]);
                    // fall-through
                case preg_match('#([^%:\s]+)::([\w\._]+)#', $val, $match):
                    // with class real name
                    $class = isset($class) ? $class : $match[1];
                    $method = $match[2];
                    if (is_callable(array($class, $method))) {
                        $val = $class::$method($datagridName, $key);
                    }
                    if (defined("$class::$method")) {
                        $val = constant("$class::$method");
                    }
                    break;
                // service method call @service->method
                case preg_match('#@([\w\._]+)->([\w\._]+)#', $val, $match):
                    $service = $match[1];
                    $method = $match[2];
                    $val = $this->container
                        ->get($service)
                        ->$method($datagridName, $key);
                    break;
                default:
                    break;
            }

            $datagridDefinition[$key] = $val;
        }

        return $datagridDefinition;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
