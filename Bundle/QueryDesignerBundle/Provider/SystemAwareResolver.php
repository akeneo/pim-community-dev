<?php

namespace Oro\Bundle\QueryDesignerBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @param array $config
     */
    public function resolve(&$config)
    {
        array_walk_recursive(
            $config,
            function (&$val, $key) {
                $this->resolveSystemCall($val, $key);
            }
        );
    }

    /**
     * Replace static call, service call or constant access notation to value they returned
     * while building datagrid
     *
     * @param string $val value to be resolved/replaced
     * @param string $key key from datagrid definition (columns, filters, sorters, etc)
     *
     * @return string
     */
    protected function resolveSystemCall(&$val, $key)
    {
        // resolve only scalar value, if it's not - value was already resolved
        // this can happen in case of extended grid definitions
        if (!is_scalar($val)) {
            return $val;
        }

        switch (true) {
            // a parameter %name%
            case preg_match('#%([\w\._]+)%#', $val, $match):
                $val = $this->container->getParameter($match[1]);
                break;
            // static call class:method or class::const
            case preg_match('#%([\w\._]+)%::([\w\._]+)#', $val, $match):
                // with class as param
                $class = $this->container->getParameter($match[1]);
                // fall-through
            case preg_match('#([^\'"%:\s]+)::([\w\._]+)#', $val, $match):
                // with class real name
                $class = isset($class) ? $class : $match[1];

                $method = $match[2];
                if (is_callable([$class, $method])) {
                    $val = $class::$method();
                }
                if (defined("$class::$method")) {
                    $_val = constant("$class::$method");
                    if (is_string($_val)) {
                        $val = str_replace($match[0], $_val, $val);
                    } else {
                        $val = $_val;
                    }
                }
                break;
            // service method call @service->method
            case preg_match('#@([\w\._]+)->([\w\._]+)#', $val, $match):
                $service = $match[1];
                $method  = $match[2];
                $val     = $this->container
                    ->get($service)
                    ->$method();
                break;
            // service pass @service
            case preg_match('#@([\w\._]+)#', $val, $match):
                $service = $match[1];
                $val     = $this->container->get($service);
                break;
            default:
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
