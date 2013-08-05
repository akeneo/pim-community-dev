<?php

namespace Pim\Bundle\ProductBundle\Exception;

use Pim\Bundle\ProductBundle\Model\ProductInterface;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingIdentifierException extends \Exception
{
    public function __construct(ProductInterface $product)
    {
        parent::__construct(sprintf('Product %d has no identifier attribute', $product->getId()));
    }
}
