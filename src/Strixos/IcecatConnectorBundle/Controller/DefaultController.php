<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 *
 * Enter description here ...
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DefaultController extends Controller
{
    /**
     * Display list of suppliers
     *
     * @Route("/default/index")
     * @Template()
     */
    public function indexAction()
    {
        return new Response('TODO : allow to define config here');
    }

}
