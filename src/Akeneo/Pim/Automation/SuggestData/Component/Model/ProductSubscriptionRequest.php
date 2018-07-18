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

namespace Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Holds a ProductInterface, and provides its values given a defined mapping
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionRequest
{
    /** @var ProductInterface */
    private $product;

    /**
     * @param ProductInterface $product
     */
    public function __construct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * Returns the product values corresponding to the provided mapping
     *
     * @param IdentifiersMapping $mapping
     *
     * @return array
     */
    public function getMappedValues(IdentifiersMapping $mapping): array
    {
        $mapped = [];
        foreach ($mapping as $pimAiCode => $mappedAttribute) {
            if ($mappedAttribute->isScopable() || $mappedAttribute->isLocalizable(
                ) || $mappedAttribute->isLocaleSpecific()) {
                throw new \LogicException(
                    sprintf(
                        'Mapped attribute "%s" for code "%s" should not be localizable, scopable nor locale specific',
                        $mappedAttribute->getCode(),
                        $pimAiCode
                    )
                );
            }
            $value = $this->product->getValue($mappedAttribute->getCode());
            if (null !== $value && $value->hasData()) {
                $mapped[$pimAiCode] = $value->__toString();
            }
        }

        return $mapped;
    }
}
