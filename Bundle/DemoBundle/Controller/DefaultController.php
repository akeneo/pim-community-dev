<?php

namespace Oro\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/{gridName}", name="oro_demo_index")
     * @Template
     */
    public function indexAction($gridName)
    {
        return array(
            'gridName' => $gridName
        );
    }

    /**
     * @Route("/grid/adminUsers", name="oro_demo_admin_users")
     * @Template
     */
    public function adminUsersAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Oro\Bundle\UserBundle\Entity\Role $adminRole */
        $role = $em->getRepository('OroUserBundle:Role')->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));

        return array(
            'gridName' => 'adminUsers',
            'params'   => array(
                'roleId' => $role->getId()
            )
        );
    }
}
