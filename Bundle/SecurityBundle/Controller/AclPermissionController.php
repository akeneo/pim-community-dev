<?php

namespace Oro\Bundle\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AclPermissionController extends Controller
{
    /**
     * @Route("/acl-access-levels/{oid}", name="oro_security_access_levels")
     */
    public function aclAccessLevelsAction($oid)
    {
        $levels = $this
            ->get('oro_security.acl.manager')
            ->getAccessLevels($oid);
        $translator = $this->get('translator');
        foreach ($levels as $id => $label) {
            $levels[$id] = $translator->trans('oro.security.access-level.' . $label);
        }

        return new JsonResponse(
            $levels
        );
    }
}
