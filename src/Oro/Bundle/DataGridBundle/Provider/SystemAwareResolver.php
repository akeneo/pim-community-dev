<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemAwareResolver implements ContainerAwareInterface
{
    /**W
     * @var ContainerInterface
     */
    protected $container;

    /** @var array parent configuration array node */
    protected $parentNode;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param string $datagridName
     * @param array  $datagridDefinition
     *
     * @return array
     */
    public function resolve($datagridName, $datagridDefinition)
    {
        foreach ($datagridDefinition as $key => $val) {
            if (is_array($val)) {
                $this->parentNode = $val;
                $datagridDefinition[$key] = $this->resolve($datagridName, $val);
                continue;
            }

            $val = $this->resolveSystemCall($datagridName, $key, $val);
            if ('extend' === $key) {
                // get parent grid definition, resolved
                $definition = $this->container
                    ->get('oro_datagrid.datagrid.manager')
                    ->getConfigurationForGrid($val);

                // merge them and remove extend directive
                $datagridDefinition = array_merge_recursive(
                    $definition->toArray(),
                    $datagridDefinition
                );
                unset($datagridDefinition['extend']);

                // run resolve again on merged grid definition
                $datagridDefinition = $this->resolve($val, $datagridDefinition);

                // break current loop cause we've just extended grid definition
                break;
            }

            $datagridDefinition[$key] = $val;
        }

        return $datagridDefinition;
    }

    /**
     * Replace static call, service call or constant access notation to value they returned
     * while building datagrid
     *
     * @param string $datagridName
     * @param string $key key from datagrid definition (columns, filters, sorters, etc)
     * @param string $val value to be resolved/replaced
     *
     * @return string
     */
    protected function resolveSystemCall($datagridName, $key, $val)
    {
        // resolve only scalar value, if it's not - value was already resolved
        // this can happen in case of extended grid definitions
        if (!is_scalar($val)) {
            return $val;
        }

        switch (true) {
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
                    $val = $class::$method($datagridName, $key);
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
                    ->$method(
                        $datagridName,
                        $key,
                        $this->parentNode
                    );
                break;
            // service pass @service
            case preg_match('#@([\w\._]+)#', $val, $match):
                $service = $match[1];
                $val = $this->container->get($service);
                break;
            default:
                break;
        }

        return $val;
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
