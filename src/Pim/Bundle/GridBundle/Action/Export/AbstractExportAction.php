<?php

namespace Pim\Bundle\GridBundle\Action\Export;

/**
 * Abstract export action class for datagrid managers
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @see       Pim\Bundle\GridBundle\Action\Export\ExportActionInterface
 */
abstract class AbstractExportAction implements ExportActionInterface
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->assertRequiredOptions(array('name'));
        $this->defineDefaultValues();
    }

    /**
     * Define the default values for each export action if needed
     */
    protected function defineDefaultValues()
    {
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
     * Assert the required options
     *
     * @param array $requiredOptions
     *
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
