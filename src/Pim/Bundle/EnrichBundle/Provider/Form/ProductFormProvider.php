<?php

namespace Pim\Bundle\EnrichBundle\Provider\Form;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Form provider for product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($product)
    {
        return 'pim-product-edit-form';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof ProductInterface;
    }
}
