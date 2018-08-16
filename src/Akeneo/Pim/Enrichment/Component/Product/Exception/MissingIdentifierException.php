<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Exception for missing product identifier
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingIdentifierException extends \Exception
{
    /**
     * Constructor
     *
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        parent::__construct(sprintf('Product %s has no identifier attribute', $product->getId()));
    }
}
