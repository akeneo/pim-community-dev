<?php

namespace Oro\Bundle\GridBundle\Filter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class FilterFactory implements FilterFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $types;

    /**
     * @param ContainerInterface $container
     * @param array $types
     */
    public function __construct(ContainerInterface $container, array $types = array())
    {
        $this->container = $container;
        $this->types     = $types;
    }

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @return FilterInterface
     * @throws \RunTimeException
     */
    public function create($name, $type, array $options = array())
    {
        if (!$type) {
            throw new \RunTimeException('The type must be defined');
        }

        $id = isset($this->types[$type]) ? $this->types[$type] : false;

        if (!$id) {
            throw new \RunTimeException(sprintf('No attached service to type named `%s`', $type));
        }

        /** @var $filter FilterInterface */
        $filter = $this->container->get($id);

        if (!$filter instanceof FilterInterface) {
            throw new \RunTimeException(sprintf('The service `%s` must implement `FilterInterface`', $id));
        }

        $filter->initialize($name, $options);

        return $filter;
    }
}
