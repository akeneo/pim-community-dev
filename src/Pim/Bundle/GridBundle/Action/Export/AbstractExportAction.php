<?php

namespace Pim\Bundle\GridBundle\Action\Export;

use Pim\Bundle\GridBundle\Action\Export\ExportActionInterface;

abstract class AbstractExportAction implements ExportActionInterface
{
    protected $options = array();

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->assertRequiredOptions(array('name', 'baseUrl'));
    }

    public function getRoute()
    {
        return $this->getOption('route');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getOption('name');
    }

    /**
     * {@inheritdoc}
     */
    public function getAclResource()
    {
        return $this->getOption('acl_resource');
    }

    /**
     * {@inheritdoc}
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
                        sprintf('Option "%s" is required for export action "%s"', $optionName, $this->getName())
                    );
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Option "%s" is required for export action class %s', $optionName, get_called_class())
                    );
                }
            }
        }
    }
}
