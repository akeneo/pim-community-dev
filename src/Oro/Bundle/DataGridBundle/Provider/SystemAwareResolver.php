<?php

namespace Oro\Bundle\DataGridBundle\Provider;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SystemAwareResolver implements ContainerAwareInterface
{
    const PARAMETER_REGEX = '#%([\w\._]+)%#';
    const STATIC_METHOD_REGEX = '#%([\w\._]+)%::([\w\._]+)#';
    const STATIC_METHOD_CLEAN_REGEX = '#([^\'"%:\s]+)::([\w\._]+)#';
    const SERVICE_METHOD = '#@([\w\._]+)->([\w\._]+)(\((.*)\))*#';
    const SERVICE = '#@([\w\._]+)#';

    /**
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
            case preg_match(static::PARAMETER_REGEX, $val, $match):
                $val = $this->container->getParameter($match[1]);
                break;
            // static call class:method or class::const
            case preg_match(static::STATIC_METHOD_REGEX, $val, $match):
                // with class as param
                $class = $this->container->getParameter($match[1]);
                // fall-through
                // no break
            case preg_match(static::STATIC_METHOD_CLEAN_REGEX, $val, $match):
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
            // service method call @service->method, @service->method(argument), @service->method(@other.service->method)
            case preg_match(static::SERVICE_METHOD, $val, $match):
                $val = $this->executeMethod($val, [$datagridName, $key, $this->parentNode]);
                break;
            // service pass @service
            case preg_match(static::SERVICE, $val, $match):
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

    /**
     * @param string $expression
     * @param array  $optionsArguments
     *
     * @return mixed
     */
    protected function executeMethod($expression, array $optionsArguments = [])
    {
        preg_match(static::SERVICE_METHOD, $expression, $matches);
        $service = $matches[1];
        $method = $matches[2];
        $arguments = [];

        if (isset($matches[4]) && !empty($matches[4])) {
            $arguments = explode(',', $matches[4]);

            $newArguments = [];
            foreach ($arguments as $argument) {
                if (0 === strpos(trim($argument), '@')) {
                    $newArguments[] = $this->executeMethod($argument);
                } else {
                    $newArguments[] = $argument;
                }
            }

            $arguments = array_merge($newArguments, $optionsArguments);
        }

        return call_user_func_array([$this->container->get($service), $method], $arguments);
    }
}
