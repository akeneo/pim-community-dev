<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Form\Type\BatchProductType;
use Pim\Bundle\CatalogBundle\Model\BatchProduct;

/**
 * Batch operation controller
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BatchOperationController extends Controller
{
    /**
     * @Template
     */
    public function chooseAction(Request $request)
    {
        $batchProduct = new BatchProduct();

        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $products = $this->getProductManager()->getFlexibleRepository()->findByIds($productIds);
            $batchProduct->setProducts($products);
        }

        $form = $this->createForm(new BatchProductType, $batchProduct);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                return $this->redirectToRoute(
                    'pim_catalog_batch_operation_configure',
                    array(
                        'products'       => $batchProduct->getProductIds(),
                        'operationAlias' => $batchProduct->getOperationAlias(),
                    )
                );
            }
        }

        return array(
            'form'         => $form->createView(),
            'batchProduct' => $batchProduct,
        );
    }

    /**
     * @Template
     */
    public function configureAction(Request $request, $operationAlias)
    {
        $batchProduct = new BatchProduct();
        $batchProduct->setOperationAlias($operationAlias);

        if ($request->isMethod('GET')) {
            $productIds = $request->query->get('products');
            if (!$productIds || !is_array($productIds)) {
                return $this->redirectToRoute('pim_catalog_product_index');
            }
            $products = $this->getProductManager()->getFlexibleRepository()->findByIds($productIds);
            $batchProduct->setProducts($products);
        }

        $form = $this->createForm(new BatchProductType, $batchProduct);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $batchProduct->performOperation();
                $this->flush();
            }
        }

        return array(
            'form'         => $form->createView(),
            'batchProduct' => $batchProduct,
        );
    }
}
