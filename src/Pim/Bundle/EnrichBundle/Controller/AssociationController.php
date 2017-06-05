<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
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
    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var \Pim\Component\Catalog\Builder\ProductBuilderInterface */
    protected $productBuilder;

    /** @var EngineInterface */
    protected $templating;

    /**
     * @param AssociationTypeRepositoryInterface $assocTypeRepository
     * @param ProductRepositoryInterface         $productRepository
     * @param ProductBuilderInterface            $productBuilder
     * @param EngineInterface                    $templating
     */
    public function __construct(
        AssociationTypeRepositoryInterface $assocTypeRepository,
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        EngineInterface $templating
    ) {
        $this->assocTypeRepository = $assocTypeRepository;
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->templating = $templating;
    }

    /**
     * Display association grids
     *
     * @param Request $request the request
     * @param int     $id      the product id (owner)
     *
     * @AclAncestor("pim_enrich_associations_view")
     *
     * @return Response
     */
    public function associationsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $associationTypes = $this->assocTypeRepository->findAll();

        return $this->templating->renderResponse(
            'PimEnrichBundle:Association:_associations.html.twig',
            [
                'product'          => $product,
                'associationTypes' => $associationTypes,
                'dataLocale'       => $request->get('dataLocale', null)
            ]
        );
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @throws NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);
        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', $id)
            );
        }
        $this->productBuilder->addMissingAssociations($product);

        return $product;
    }
}
