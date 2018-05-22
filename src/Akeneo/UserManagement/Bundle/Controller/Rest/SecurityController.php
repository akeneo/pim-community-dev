<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Security rest controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SecurityController
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var AclAnnotationProvider */
    protected $aclProvider;

    /**
     * @param SecurityFacade        $securityFacade
     * @param AclAnnotationProvider $aclProvider
     */
    public function __construct(SecurityFacade $securityFacade, AclAnnotationProvider $aclProvider)
    {
        $this->securityFacade = $securityFacade;
        $this->aclProvider = $aclProvider;
    }

    /**
     * @return JsonResponse
     */
    public function getAction()
    {
        $securityFacade = $this->securityFacade;
        $annotations = $this->aclProvider->getAnnotations();
        $result = [];

        foreach ($annotations as $annotation) {
            $result[$annotation->getId()] = $securityFacade->isGranted($annotation->getId());
        }

        return new JsonResponse($result);
    }
}
