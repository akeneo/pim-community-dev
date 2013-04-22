<?php
namespace Pim\Bundle\ProductBundle\Controller;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use YsTools\BackUrlBundle\Annotation\BackUrl;

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
     * @param Request $request
     *
     * @Route("/index.{_format}",
     *      name="pim_product_productattribute_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('a.id', 'a.code', 'a.attributeType', 'a.scopable', 'a.translatable')
            ->from('PimProductBundle:ProductAttribute', 'a')
            ->where("a.entityType = 'Pim\Bundle\ProductBundle\Entity\Product'");

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_product.productattribute_grid_manager.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $gridManager AttributeDatagridManager */
        $gridManager = $this->get('pim_product.productattribute_grid_manager');
        $datagrid = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimProductBundle:ProductAttribute:index.html.twig';
        }

        return $this->render(
            $view,
            array(
                'datagrid' => $datagrid,
                'form'     => $datagrid->getForm()->createView()
            )
        );
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

        $em = $this->container->get('doctrine.orm.entity_manager');
        $locales = $em->getRepository('PimConfigBundle:Language')->findBy(array('activated' => 1));
        $disabledLocales = $em->getRepository('PimConfigBundle:Language')->findBy(array('activated' => 0));

        return array(
            'form' => $this->get('pim_product.form.attribute')->createView(),
            'locales' => $locales,
            'disabledLocales' => $disabledLocales,
            'measures' => $this->container->getParameter('oro_measure.measures_config')
        );
    }

    /**
     * Remove attribute
     *
     * @param Attribute $entity
     *
     * @Route("/remove/{id}", requirements={"id"="\d+"})
     *
     * @BackUrl("back")
     */
    public function removeAction(ProductAttribute $entity)
    {
        $em = $this->getProductManager()->getStorageManager();
        $em->remove($entity);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Attribute successfully removed');

        return $this->redirect($this->generateUrl('pim_product_productattribute_index'));
    }

}
