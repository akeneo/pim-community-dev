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
        $levels = $this
            ->get('oro_security.acl.manager')
            ->getAccessLevelsForObject($this->getRequest()->get('oid'));
        $translator = $this->get('translator');
        foreach ($levels as $id => $label) {
            $levels[$id] = $translator->trans('oro.security.access-level.' . $label);
        }

        return new JsonResponse(
            $levels
        );
    }
}
