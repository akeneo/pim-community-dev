<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Controller;

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
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('pim_connectoricecat_supplier_list'));
    }

}
