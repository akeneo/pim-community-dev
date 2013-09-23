<?php

namespace Pim\Bundle\CatalogBundle\MassEditAction;

/**
 * Operation to execute on a set of products
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassEditAction
{
    /**
     * Get the form type to use in order to configure the operation
     *
     * @return string|FormTypeInterface
     */
    public function getFormType();

    /**
     * Get the form options to configure the operation
     *
     * @return array
     */
    public function getFormOptions();

    /**
     * Initialize the operation with the products
     *
     * @param ProductInterface[] $products
     */
    public function initialize(array $products);

    /**
     * Perform an operation on a set of products
     *
     * @param ProductInterface[] $products
     */
    public function perform(array $products);
}
