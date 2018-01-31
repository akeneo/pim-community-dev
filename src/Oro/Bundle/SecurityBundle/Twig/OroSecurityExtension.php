<?php

namespace Oro\Bundle\SecurityBundle\Twig;

use Oro\Bundle\SecurityBundle\SecurityFacade;

class OroSecurityExtension extends \Twig_Extension
{
    /**
     * @var SecurityFacade
     */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('resource_granted', [$this, 'checkResourceIsGranted']),
        ];
    }

    /**
     * Check if ACL resource is granted for current user
     *
     * @param string|string[] $attributes Can be a role name(s), permission name(s), an ACL annotation id
     *                                    or something else, it depends on registered security voters
     * @param mixed $object A domain object, object identity or object identity descriptor (id:type)
     *
     * @return bool
     */
    public function checkResourceIsGranted($attributes, $object = null)
    {
        return $this->securityFacade->isGranted($attributes, $object);
    }
}
