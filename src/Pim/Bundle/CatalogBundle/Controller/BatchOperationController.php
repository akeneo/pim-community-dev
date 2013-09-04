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
    public function configureAction(Request $request)
    {
        $productIds = $request->query->get('batch[products]', null, true);
        if (!$productIds || !is_array($productIds)) {
            return $this->redirectToRoute('pim_catalog_product_index');
        }
        $products = $this->getProductManager()->getFlexibleRepository()->findByIds($productIds);

        $batchProduct = new BatchProduct;
        $batchProduct->setProducts($products);
        if ($operation = $request->query->get('batch[operation]', null, true)) {
            $batchProduct->setOperation($operation);
        }

        $form = $this->createForm(new BatchProductType, $batchProduct);

        return array(
            'form'         => $form->createView(),
            'batchProduct' => $batchProduct
        );
    }
}
