<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param ManagerRegistry $doctrine
     * @param EngineInterface $templating
     * @param ProductManager  $productManager
     */
    public function __construct(
        ManagerRegistry $doctrine,
        EngineInterface $templating,
        ProductManager $productManager
    ) {
        $this->doctrine       = $doctrine;
        $this->templating     = $templating;
        $this->productManager = $productManager;
    }

    /**
     * Display association grids
     *
     * @param Request $request the request
     * @param integer $id      the product id (owner)
     *
     * @AclAncestor("pim_enrich_associations_view")
     *
     * @return Response
     */
    public function associationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $this->productManager->ensureAllAssociationTypes($product);

        $associationTypes = $this->doctrine->getRepository('PimCatalogBundle:AssociationType')->findAll();

        return $this->templating->renderResponse(
            'PimEnrichBundle:Association:_associations.html.twig',
            array(
                'product'          => $product,
                'associationTypes' => $associationTypes,
                'dataLocale'       => $request->get('dataLocale', null)
            )
        );
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
                sprintf('Product with id %s could not be found.', $id)
            );
        }

        return $product;
    }
}
