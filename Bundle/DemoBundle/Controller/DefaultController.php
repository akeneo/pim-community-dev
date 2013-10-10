<?php

namespace Oro\Bundle\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Template
     */
    public function indexAction()
    {
        $manager = $this->get('oro_grid.datagrid.manager');
        return array(
            'manager' => $manager
        );
    }
}
