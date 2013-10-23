<?php

namespace Oro\Bundle\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
class AclPermissionController extends Controller
{
    /**
     * @Route("/acl-access-levels", name="oro_security_access_levels")
     */
    public function aclPermissionAccessLevelsAction()
    {
        return new JsonResponse($this->get('oro_security.acl.manager')->getAccessLevelsForObject($this->getRequest()->get('oid')));
    }
}
