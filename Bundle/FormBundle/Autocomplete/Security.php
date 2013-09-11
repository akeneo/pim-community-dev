<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

use Oro\Bundle\UserBundle\Acl\ManagerInterface;

class Security
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var array
     */
    protected $autocompleteAclResources;

    public function __construct(ManagerInterface $manager = null)
    {
        $this->manager = $manager;
        $this->autocompleteAclResources = array();
    }

    /**
     * @param string $name
     * @param string $aclResource
     */
    public function setAutocompleteAclResource($name, $aclResource)
    {
        $this->autocompleteAclResources[$name] = $aclResource;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getAutocompleteAclResource($name)
    {
        return isset($this->autocompleteAclResources[$name]) ? $this->autocompleteAclResources[$name] : null;
    }

    /**
     * @param $name
     * @return boolean
     */
    public function isAutocompleteGranted($name)
    {
        $aclResource = $this->getAutocompleteAclResource($name);
        return $aclResource && $this->manager->isResourceGranted($aclResource);
    }
}
