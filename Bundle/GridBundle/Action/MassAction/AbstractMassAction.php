<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

abstract class AbstractMassAction implements MassActionInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Required options: name
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->assertRequiredOptions(array('name'));
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

    /**
     * @param array $requiredOptions
     * @throws \InvalidArgumentException
     */
    protected function assertRequiredOptions(array $requiredOptions)
    {
        foreach ($requiredOptions as $optionName) {
            if (!isset($this->options[$optionName])) {
                $actionName = $this->getName();
                if ($actionName) {
                    throw new \InvalidArgumentException(
                        sprintf('Option "%s" is required for mass action "%s"', $optionName, $this->getName())
                    );
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Option "%s" is required for mass action class %s', $optionName, get_called_class())
                    );
                }
            }
        }
    }
}
