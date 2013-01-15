<?php
namespace Pim\Bundle\FlexibleProductBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;

/**
 * Product attribute controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @Route("/productattribute")
 */
class ProductAttributeController extends Controller
{

    /**
     * @return ProductManager
     */
    protected function getProductManager()
    {
        return $this->get('pim_flexibleproduct.product_manager');
    }

    /**
     * @return DocumentManager
     */
    protected function getStorageManager()
    {
        return $this->getProductManager()->getStorageManager();
    }

    /**
     * Lists all attributes
     *
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $attributes = $this->getProductManager()->getFlexibleAttributeRepository()->findAll();

        return array('productAttributes' => $attributes);
    }

}