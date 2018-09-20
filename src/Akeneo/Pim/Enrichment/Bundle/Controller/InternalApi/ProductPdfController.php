<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Exception\RendererRequiredException;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductPdfRenderer;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product Controller
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPdfController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RendererRegistry */
    protected $rendererRegistry;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param RendererRegistry           $rendererRegistry
     */
    public function __construct(ProductRepositoryInterface $productRepository, RendererRegistry $rendererRegistry)
    {
        $this->productRepository = $productRepository;
        $this->rendererRegistry = $rendererRegistry;
    }

    /**
     * Generate Pdf and send it to the client for specific product
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_pdf_generator_product_download")
     *
     * @throws HttpException
     *
     * @return Response
     */
    public function downloadPdfAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $renderingDate = new \DateTime('now');

        try {
            $responseContent = $this->rendererRegistry->render(
                $product,
                ProductPdfRenderer::PDF_FORMAT,
                [
                    'locale'        => $request->get('dataLocale', null),
                    'renderingDate' => $renderingDate,
                    'scope'         => $request->get('dataScope', null),
                ]
            );
        } catch (RendererRequiredException $e) {
            throw new HttpException(500, 'Unable to generate the product PDF', $e);
        }

        return new Response(
            $responseContent,
            200,
            [
                'content-type'        => 'application/pdf',
                'content-disposition' => sprintf(
                    'attachment; filename=%s-%s.pdf',
                    $product->getIdentifier(),
                    $renderingDate->format('Y-m-d_H-i-s')
                ),
            ]
        );
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productRepository->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }
}
