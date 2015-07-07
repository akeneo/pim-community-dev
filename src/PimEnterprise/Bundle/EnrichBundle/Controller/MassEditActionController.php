<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Pim\Bundle\EnrichBundle\Controller\MassEditActionController as BaseMassEditActionController;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mass edit operation controller
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class MassEditActionController extends BaseMassEditActionController
{
    /**
     * Override to apply permissions per category
     *
     * {@inheritdoc}
     */
    protected function isExecutable()
    {
        $gridName = $this->request->get('gridName');
        $isValid = parent::isExecutable();
        if ($gridName !== 'product-grid') {
            return $isValid;
        } else {
            return $this->editableProducts();
        }
    }

    /**
     * Check if the products are editable, add a flash message with 10 first non-editable products
     *
     * @return boolean
     */
    protected function editableProducts()
    {
        $products = $this->getObjects();
        $notEditable = [];
        foreach ($products as $product) {
            if (is_array($product)) {
                $product = $product[0];
            }

            if ($this->securityContext->isGranted(Attributes::EDIT, $product) === false) {
                $notEditable[] = $product->getIdentifier();
            }
            if (count($notEditable) > 9) {
                break;
            }
        }
        if (count($notEditable) > 0) {
            $this->addFlash(
                'error',
                'pim_enrich.mass_edit_action.product.not_editable',
                ['%identifiers%' => implode(', ', $notEditable)]
            );

            return false;
        }

        return true;
    }
}
