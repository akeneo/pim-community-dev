<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

abstract class AbstractMassAction implements MassActionInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getOption('name');
    }

    /**
     * {@inheritDoc}
     */
    public function getAclResource()
    {
        return $this->getOption('acl_resource');
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }
}
