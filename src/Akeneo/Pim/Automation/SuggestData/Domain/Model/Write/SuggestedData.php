<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Write;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class SuggestedData
{
    /** @var array */
    private $suggestedValues;

    /** @var ProductInterface */
    private $product;

    /**
     * @param array $suggestedValues
     * @param ProductInterface $product
     */
    public function __construct(
        array $suggestedValues,
        ProductInterface $product
    ) {
        $this->suggestedValues = $suggestedValues;
        $this->product = $product;
    }

    /**
     * @return array
     */
    public function getSuggestedValues(): array
    {
        return $this->suggestedValues;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
    }
}
