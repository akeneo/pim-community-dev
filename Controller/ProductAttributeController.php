<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Product attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/product-attribute")
 */
class ProductAttributeController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        $pm = $this->container->get('product_manager');
        // force data locale if provided
        $dataLocale = $this->getRequest()->get('dataLocale');
        $pm->setLocale($dataLocale);
        // force data scope if provided
        $dataScope = $this->getRequest()->get('dataScope');
        $dataScope = ($dataScope) ? $dataScope : 'ecommerce';
        $pm->setScope($dataScope);

        return $pm;
    }

    /**
     * List product attributes
     * @Route("/index")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $attributes = $this->getProductManager()->getAttributeExtendedRepository()->findBy(array());

        return array('attributes' => $attributes);
    }

    /**
     * Create attribute
     *
     * @Route("/create")
     * @Template("PimProductBundle:ProductAttribute:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $attribute = $this->getProductManager()->createAttributeExtended();

        return $this->editAction($attribute);
    }

    /**
     * Edit attribute form
     *
     * @param ProductAttribute $entity
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(ProductAttribute $entity)
    {
        if ($this->get('pim_product.form.handler.attribute')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Attribute successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_product_productattribute_edit', array('id' => $entity->getId()))
            );
        }

        return array(
            'form' => $this->get('pim_product.form.attribute')->createView()
        );
    }

    /**
     * Remove attribute
     *
     * @param Attribute $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return array
     */
    public function removeAction(ProductAttribute $entity)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Attribute successfully removed');

        return $this->redirect($this->generateUrl('pim_product_productattribute_index'));
    }

    /**
     * List attributes group by AttributeGroup asked
     * - groupId = 0 => get all product attributes
     * - groupId > 0 => get product attributes by group id
     * - groupId = null => get unclassified product attributes
     *
     * @param integer $groupId
     *
     * @Route("/list/{groupId}", requirements={"groupId"="\d"}, defaults={"groupId"=null})
     * @Template("PimProductBundle:ProductAttribute:index.html.twig")
     *
     * @return multitype:ProductAttribute
     */
    public function listAction($groupId = null)
    {
        $criterias = array();
        if ($groupId > 0) {
            $criterias = array('group' => $groupId);
        } elseif ($groupId === null) {
            $criterias = array('group' => null);
        }

        return array(
            'attributes' => $this->getProductManager()->getAttributeExtendedRepository()->findBy($criterias)
        );
    }
}
