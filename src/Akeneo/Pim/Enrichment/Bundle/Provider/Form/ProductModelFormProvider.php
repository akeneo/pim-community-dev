<?php

namespace Akeneo\Pim\Enrichment\Bundle\Provider\Form;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;

/**
 * Form provider for product model
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductModelFormProvider implements FormProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getForm($productModel): string
    {
        return 'pim-product-model-edit-form';
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element): bool
    {
        return $element instanceof ProductModelInterface;
    }
}
