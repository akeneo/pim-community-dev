<?php

namespace Oro\Bundle\GridBundle\Action;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $aclResource = null;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var ManagerInterface
     */
    protected $aclManager;

    /**
     * @var bool
     */
    protected $isProcessed = false;

    /**
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * @param ManagerInterface $aclManager
     */
    public function __construct(ManagerInterface $aclManager)
    {
        $this->aclManager   = $aclManager;
    }

    /**
     * Filter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Action type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource()
    {
        return $this->aclResource;
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $aclResource
     */
    public function setAclResource($aclResource)
    {
        $this->aclResource = $aclResource;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        $this->assertHasRequiredOptions();
    }

    protected function assertHasRequiredOptions()
    {
        foreach ($this->requiredOptions as $optionName) {
            $this->assertHasRequiredOption($optionName);
        }
    }

    /**
     * @param string $optionName
     * @throws \LogicException
     */
    protected function assertHasRequiredOption($optionName)
    {
        if (!isset($this->options[$optionName])) {
            throw new \LogicException(
                'There is no option "' . $optionName . '" for action "' . $this->name . '".'
            );
        }
    }

    /**
     * Check whether action allowed for current user
     *
     * @return bool
     */
    public function isGranted()
    {
        if ($this->aclResource) {
            return $this->aclManager->isResourceGranted($this->aclResource);
        }

        return true;
    }
}
