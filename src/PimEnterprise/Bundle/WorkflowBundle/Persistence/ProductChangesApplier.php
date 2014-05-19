<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Serializer\ProductNormalizer;

/**
 * Applies product changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductChangesApplier
{
    protected $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function apply(AbstractProduct $product, array $changes)
    {
        $this->denormalizer->denormalize($changes, 'product', ProductNormalizer::FORMAT, ['instance' => $product]);
    }
}
