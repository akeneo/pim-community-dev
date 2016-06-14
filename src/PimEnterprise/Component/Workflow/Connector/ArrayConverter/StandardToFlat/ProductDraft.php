<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\StandardToFlat\Product;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductDraft implements ArrayConverterInterface
{
    /** @var Product */
    protected $productConverter;

    /**
     * @param Product $productConverter
     */
    public function __construct(Product $productConverter)
    {
        $this->productConverter = $productConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        return $this->productConverter->convert($item, $options);
    }
}
