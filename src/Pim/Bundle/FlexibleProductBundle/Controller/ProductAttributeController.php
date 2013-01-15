<?php
namespace Pim\Bundle\FlexibleProductBundle\Controller;

use Pim\Bundle\FlexibleProductBundle\Form\Type\ProductAttributeType;

use Pim\Bundle\FlexibleProductBundle\Entity\ProductAttribute;

use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->get('pim_flexibleproduct.product_manager');
    }

    /**
     * @return Doctrine\ODM\MongoDB\DocumentManager
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

    /**
     * Displays a form to create a new attribute
     *
     * @Route("/new")
     * @Template()
     *
     * @return multitype
     */
    public function newAction()
    {
        $attribute = $this->getProductManager()->createFlexibleAttribute();
        $form = $this->createAttributeForm($attribute);

        // render form
        return array('entity' => $attribute, 'form' => $form->createView());
    }

    /**
     * Create a new attribute
     *
     * @param Request $request the request
     *
     * @Route("/create")
     * @Method("POST")
     *
     * @return multitype
     */
    public function createAction(Request $request)
    {
        $attribute = $this->getProductManager()->createFlexibleAttribute();
        $form = $this->createAttributeForm($attribute);

        $form->bind($request);

        // validation
        if ($form->isValid()) {
            $manager = $this->getProductManager()->getStorageManager();

            try {
                $manager->persist($attribute);
                $manager->flush();

                $this->get('session')->setFlash('success', 'attribute %code% has been created');

            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }
        }

        // render form
        return $this->render(
            'PimFlexibleProductBundle:ProductAttribute:new.html.twig',
            array(
                'entity' => $attribute,
                'form'   => $form->createView()
            )
        );
    }

    /**
     * Create attribute form
     * @param string $attribute
     *
     * @return Symfony\Component\Form\Form
     */
    protected function createAttributeForm($attribute)
    {
        // get classes fullname
        $attClassFullname = $this->getProductManager()->getAttributeName();
        $prodAttClassFullname = $this->getProductManager()->getFlexibleAttributeName();

        // create form
        $form = $this->createForm(new ProductAttributeType($prodAttClassFullname, $attClassFullname), $attribute);

        return $form;
    }

}