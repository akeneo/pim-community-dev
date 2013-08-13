<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

abstract class AbstractMassAction implements MassActionInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Action options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Mass action name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getOption('name');
    }

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource()
    {
        return $this->getOption('acl_resource');
    }

    /**
     * Mass action label
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->getOption('label');
    }
}
