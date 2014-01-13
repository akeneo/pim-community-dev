<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Association controller
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationController
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
     * @var Manager
     */
    protected $datagridManager;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     * @param EngineInterface   $templating
     * @param Manager           $datagridManager
     * @param ProductManager    $productManager
     * @param LocaleManager     $localeManager
     */
    public function __construct(
        RegistryInterface $doctrine,
        EngineInterface $templating,
        Manager $datagridManager,
        ProductManager $productManager,
        LocaleManager $localeManager
    ) {
        $this->doctrine        = $doctrine;
        $this->templating      = $templating;
        $this->datagridManager = $datagridManager;
        $this->productManager  = $productManager;
        $this->localeManager   = $localeManager;
    }

    /**
     * Display association grids
     *
     * @param int $id
     *
     * @AclAncestor("pim_catalog_associations_view")
     *
     * @return Response
     */
    public function associationsAction($id)
    {
        $product = $this->findProductOr404($id);

        $this->productManager->ensureAllAssociationTypes($product);

        $associationTypes = $this->doctrine->getRepository('PimCatalogBundle:AssociationType')->findAll();

        /*

        $productGrid = $this->datagridManager->getDatagridManager('association_product');
        $productGrid->setProduct($product);

        $groupGrid = $this->datagridManager->getDatagridManager('association_group');
        $groupGrid->setProduct($product);

        $associationType = null;
        if (!empty($associationTypes)) {
            $associationType = reset($associationTypes);
            $productGrid->setAssociationId($associationType->getId());
            $groupGrid->setAssociationId($associationType->getId());
        }

        $routeParameters = array('id' => $product->getId());
        $productGrid->getRouteGenerator()->setRouteParameters($routeParameters);
        $groupGrid->getRouteGenerator()->setRouteParameters($routeParameters);

        $productGridView = $productGrid->getDatagrid()->createView();
        $groupGridView   = $groupGrid->getDatagrid()->createView();
         */

        return $this->templating->renderResponse(
            'PimCatalogBundle:Association:_associations.html.twig',
            array(
                'product'                => $product,
                'associationTypes'       => $associationTypes,
                'dataLocale'             => $this->localeManager->getDataLocale(),

                // 'associationProductGrid' => $productGridView,
                // 'associationGroupGrid'   => $groupGridView,
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
     * @AclAncestor("pim_catalog_associations_view")
     * @return Response
     */
    public function listAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridManager->getDatagridManager('association_product');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridManager->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
    }

    /**
     * List group associations for the provided product
     *
     * @param Request $request The request object
     * @param integer $id      Product id
     *
     * @Template
     * @AclAncestor("pim_catalog_associations_view")
     * @return Response
     */
    public function listGroupAssociationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $datagridManager = $this->datagridManager->getDatagridManager('association_group');
        $datagridManager->setProduct($product);

        $datagridView = $datagridManager->getDatagrid()->createView();

        return $this->datagridManager->getDatagridRenderer()->renderResultsJsonResponse($datagridView);
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
