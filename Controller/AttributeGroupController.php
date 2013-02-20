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
        $groups = $this->getGroupManager()->getEntityRepository()->findAll();

        return array('groups' => $groups);
    }

    /**
     * Get group manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
     */
    protected function getGroupManager()
    {
        return $this->get('attribute_group_manager');
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
        $group = $this->getGroupManager()->createEntity();

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
        $manager = $this->getGroupManager()->getStorageManager();
        $manager->remove($group);
        $manager->flush();

        $this->get('session')->getFlashBag()->add('success', 'Group successfully removed');

        return $this->redirect($this->generateUrl('pim_product_attributegroup_index'));
    }
}
