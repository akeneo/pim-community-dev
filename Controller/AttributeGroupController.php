<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Form\Type\AttributeGroupType;

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
        $this->getEntityManager()->remove($group);
        $this->getEntityManager()->flush();

        $this->get('session')->getFlashBag()->add('success', 'Group successfully removed');

        return $this->redirect($this->generateUrl('pim_product_attributegroup_index'));
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Get attribute group repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAttributeGroupRepository()
    {
        return $this->getEntityManager()->getRepository('PimProductBundle:AttributeGroup');
    }

    /**
     * @return Pim\Bundle\TranslationBundle\Manager\TranslationManager
     */
    protected function getTranslationManager()
    {
        return $this->container->get('pim_translation.translation_manager');
    }

    /**
     * Return all locales actived
     * @return multitype:string
     */
    protected function getActiveLocales()
    {
        $locales = $this->getDoctrine()
                        ->getEntityManager()
                        ->getRepository('Pim\Bundle\ConfigBundle\Entity\Language')
                        ->findBy(array('activated' => true));

        $activeLocales = array();
        foreach ($locales as $locale) {
            $activeLocales[] = $locale->getCode();
        }

        return $activeLocales;
    }
}
