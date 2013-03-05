<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * AttributeGroup controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/attribute-group")
 */
class AttributeGroupController extends Controller
{

    /**
     * List attribute groups
     *
     * @return multitype
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $groups = $this->getAttributeGroupRepository()->findAll();

        return array('groups' => $groups);
    }

    /**
     * Get storage manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getStorageManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get AttributeGroup Repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getAttributeGroupRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:AttributeGroup');
    }

    /**
     * Create attribute group
     *
     * @Route("/create")
     * @Template("PimProductBundle:AttributeGroup:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $group = new AttributeGroup();

        return $this->editAction($group);
    }

    /**
     * Edit language
     *
     * @param AttributeGroup $group
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(AttributeGroup $group)
    {
        if ($this->get('pim_product.form.handler.attribute_group')->process($group)) {
            $this->get('session')->getFlashBag()->add('success', 'Group successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_product_attributegroup_index')
            );
        }

        return array(
            'form' => $this->get('pim_product.form.attribute_group')->createView()
        );
    }

    /**
     * Remove language
     *
     * @param AttributeGroup $group
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(AttributeGroup $group)
    {
        $this->getStorageManager()->remove($group);
        $this->getStorageManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Group successfully removed');

        return $this->redirect($this->generateUrl('pim_product_attributegroup_index'));
    }
}
