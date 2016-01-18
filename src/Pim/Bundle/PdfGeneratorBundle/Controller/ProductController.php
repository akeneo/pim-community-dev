<?php

namespace Pim\Bundle\PdfGeneratorBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\PdfGeneratorBundle\Exception\RendererRequiredException;
use Pim\Bundle\PdfGeneratorBundle\Renderer\RendererRegistry;
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
class ProductController
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
     * Constructor
     *
     * @param ProductManager   $productManager
     * @param RendererRegistry $rendererRegistry
     */
    public function __construct(ProductManager $productManager, RendererRegistry $rendererRegistry)
    {
        $this->productManager   = $productManager;
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
        $product       = $this->findProductOr404($id);
        $renderingDate = new \DateTime('now');

        try {
            $responseContent = $this->rendererRegistry->render(
                $product,
                'pdf',
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
            array(
                'content-type'        => 'application/pdf',
                'content-disposition' => sprintf(
                    'attachment; filename=%s-%s.pdf',
                    $product->getIdentifier(),
                    $renderingDate->format('Y-m-d_H-i-s')
                ),
            )
        );
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (null === $product) {
            throw new NotFoundHttpException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
    }
}
