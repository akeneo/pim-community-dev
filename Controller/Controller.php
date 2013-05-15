<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Pim\Bundle\ProductBundle\Model\AvailableProductAttributes;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Controller extends BaseController
{
    /**
     * Get the AvailbleProductAttributes form
     *
     * @param Pim\Bundle\ProductBundle\Entity\Product                   $product             The product from which to compute available attributes
     * @param Pim\Bundle\ProductBundle\Model\AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    private function getAvailableProductAttributesForm(array $attributes = array(), AvailableProductAttributes $availableAttributes = null)
    {
        return $this->createForm(
            new AvailableProductAttributesType,
            $availableAttributes ?: new AvailableProductAttributes,
            array('attributes' => $attributes)
        );
    }
}
