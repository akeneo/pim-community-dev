<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BatchOperation
{
    public function getFormType();

    public function perform(array $products);
}
