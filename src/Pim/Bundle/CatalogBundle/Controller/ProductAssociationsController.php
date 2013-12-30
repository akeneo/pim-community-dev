<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product associations controller
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationsController
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var DatagridHelperInterface
     */
    protected $datagridHelper;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param RegistryInterface       $doctrine
     * @param EngineInterface         $templating
     * @param DatagridHelperInterface $datagridHelper
     * @param ProductManager          $productManager
     */
    public function __construct(
        RegistryInterface $doctrine,
        EngineInterface $templating,
        DatagridHelperInterface $datagridHelper,
        ProductManager $productManager
    ) {
        $this->doctrine = $doctrine;
        $this->templating = $templating;
        $this->datagridHelper = $datagridHelper;
        $this->productManager = $productManager;
    }

    /**
     * Display association grids
     *
     * @param int $id
     *
     * @AclAncestor("pim_catalog_product_associations_view")
     *
     * @return Response
     */
    public function associationsAction($id)
    {
        $product = $this->findProductOr404($id);

        $this->productManager->ensureAllAssociationTypes($product);

        $associations = $this->doctrine->getRepository('PimCatalogBundle:AssociationType')->findAll();

        $productGrid = $this->datagridHelper->getDatagridManager('association_product');
        $productGrid->setProduct($product);

        $groupGrid = $this->datagridHelper->getDatagridManager('association_group');
        $groupGrid->setProduct($product);

        $association = null;
        if (!empty($associations)) {
            $association = reset($associations);
            $productGrid->setAssociationId($association->getId());
            $groupGrid->setAssociationId($association->getId());
        }

        $routeParameters = array('id' => $product->getId());
        $productGrid->getRouteGenerator()->setRouteParameters($routeParameters);
        $groupGrid->getRouteGenerator()->setRouteParameters($routeParameters);

        $productGridView = $productGrid->getDatagrid()->createView();
        $groupGridView   = $groupGrid->getDatagrid()->createView();

        return $this->templating->renderResponse(
            'PimCatalogBundle:ProductAssociations:_associations.html.twig',
            array(
                'product'                => $product,
                'associations'           => $associations,
                'associationProductGrid' => $productGridView,
                'associationGroupGrid'   => $groupGridView,
            )
        );
    }

    /**
     * List product associations for the provided product
     *
     * @param Request $request The request object
     * @param integer $id      Product id
     *
     * @Template
     * @AclAncestor("pim_catalog_product_associations_view")
     * @return Response
     */
    public function listProductAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridHelper->getDatagridManager('association_product');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
    }

    /**
     * List group associations for the provided product
     *
     * @param Request $request The request object
     * @param integer $id      Product id
     *
     * @Template
     * @AclAncestor("pim_catalog_product_associations_view")
     * @return Response
     */
    public function listGroupAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridHelper->getDatagridManager('association_group');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridHelper->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @return ProductInterface
     *
     * @throws NotFoundHttpException
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %d could not be found.', $id)
            );
        }

        return $product;
    }
}
