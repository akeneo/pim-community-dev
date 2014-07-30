<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Pim\Bundle\EnrichBundle\Controller\MassEditActionController as BaseMassEditActionController;

use Symfony\Component\HttpFoundation\Request;

use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Mass edit operation controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
