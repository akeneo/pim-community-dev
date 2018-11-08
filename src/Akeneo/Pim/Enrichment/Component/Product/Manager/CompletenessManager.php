<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Manager;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Manages completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManager
{
    /** @var CompletenessGeneratorInterface */
    protected $generator;

    /**
     * @param CompletenessGeneratorInterface $generator
     */
    public function __construct(CompletenessGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Insert missing completenesses for a given product
     *
     * @param ProductInterface $product
     */
    public function generateMissingForProduct(ProductInterface $product): void
    {
        $this->generator->generateMissingForProduct($product);
    }
}
