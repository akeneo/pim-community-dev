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
        return array(
            'resource_granted' => new \Twig_Function_Method($this, 'checkResourceIsGranted'),
        );
    }

    /**
     * Check if ACL resource is granted for current user
     *
     * @param string $aclId ACL Resource id
     *
     * @return bool
     */
    public function checkResourceIsGranted($aclId)
    {
        return $this->securityFacade->isGranted($aclId);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'oro_security_extension';
    }
}
