<?php

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Controller;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\PdfGeneratorBundle\Controller\ProductController as BaseController;

/**
 * Product Controller
 *
 * @author Charles Pourcel <charles.pourcel@akeneo.com>
 */
class ProductController extends BaseController
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var RendererRegistry
     */
    protected $rendererRegistry;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * Constructor
     *
     * @param ProductManager           $productManager
     * @param RendererRegistry         $rendererRegistry
     * @param SecurityContextInterface $securityContext
     * @param UserContext              $userContext
     */
    public function __construct(
        ProductManager $productManager,
        RendererRegistry $rendererRegistry,
        SecurityContextInterface $securityContext,
        UserContext $userContext
    ) {
        parent::__construct($productManager, $rendererRegistry);

        $this->securityContext = $securityContext;
        $this->userContext = $userContext;
    }

    /**
     * Generate Pdf for specific product
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_pdf_generator_product_download")
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function generatePdfAction(Request $request, $id)
    {
        $locale = $this->userContext->getCurrentLocale();
        $viewLocaleGranted = $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
        if (!$viewLocaleGranted) {
            throw new AccessDeniedException();
        }

        return parent::generatePdfAction($request, $id);
    }
}
