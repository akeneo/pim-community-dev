<?php

namespace Pim\Bundle\EnrichBundle\Form\View;

use Symfony\Component\Form\FormView;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Representation of the product form view
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductFormViewInterface
{
    /**
     * Add a children to the form view
     *
     * @param ProductValueInterface $value
     * @param FormView              $view
     */
    public function addChildren(ProductValueInterface $value, FormView $view);

    /**
     * Get the computed view
     *
     * @return array|FormView
     */
    public function getView();
}
