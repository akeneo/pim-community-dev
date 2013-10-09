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
                // static call @class:method
                case preg_match('#@([\w\._]+)::([\w\._]+)#', $val, $match):
                    $class = $match[1];
                    $method = $match[2];

                    $val = $class::$method($datagridName, $key);
                    break;

                // service method call @service->method
                case preg_match('#@([\w\._]+)->([\w\._]+)#', $val, $match):
                    $service = $match[1];
                    $method = $match[2];

                    $val = $this->container->get($service)->$method($datagridName, $key);
                    break;

                // constant class::method
                case preg_match('#([\w\._]+)::([\w\._]+)#i', $val, $match):
                    $class = $match[1];
                    $constant = $match[2];

                    $val = $class::$constant;
                    break;
            }

            $datagridDefinition[$key] = $val;
        }

        return $datagridDefinition;
    }

    /**
     * @param $content
     *
     * @return array
     */
    public function parse($content)
    {
        $data = Yaml::parse($content, false, true);

        if (empty($data['datagrid'])) {
            return false;
        }

        $datagridArray = $data['datagrid'];

        $iterator = new \ArrayIterator($datagridArray);
        foreach ($iterator as $datagridName => $datagrid) {
            $datagridArray[$datagridName] = $this->resolve($datagridName, $datagrid);
        }

        return $datagridArray;
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
