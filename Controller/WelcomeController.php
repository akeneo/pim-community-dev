<?php

namespace Pim\Bundle\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Welcome controller for main page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class WelcomeController extends Controller
{
    /**
     * Main page
     *
     * @Route("/index")
     * @Template()
     *
     * @return array();
     */
    public function indexAction()
    {
        return array();
    }
}
