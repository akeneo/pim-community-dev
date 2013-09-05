<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

/**
 * Operation to execute on a set of products
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BatchOperation
{
    /**
     * Get the form type to use in order to configure the application
     *
     * @return string|FormTypeInterface
     */
    public function getFormType();

    /**
     * Perform an operation on a set of products
     *
     * @param Product[]
     */
    public function perform(array $products);
}
