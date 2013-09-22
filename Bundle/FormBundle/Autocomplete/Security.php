<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

use Oro\Bundle\SecurityBundle\SecurityFacade;

class Security
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var array
     */
    protected $autocompleteAclResources;

    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
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

        return $aclResource && $this->securityFacade->isGranted($aclResource);
    }
}
