<?php
namespace Pim\Bundle\FlexibleProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/default")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     * @return mixed
     */
    public function indexAction()
    {
        $pm = $this->container->get('pim_flexibleproduct_product_manager');
        var_dump($pm);

        return $this->render('PimFlexibleProductBundle:Default:index.html.twig', array('name' => 'pouet'));
    }
}
