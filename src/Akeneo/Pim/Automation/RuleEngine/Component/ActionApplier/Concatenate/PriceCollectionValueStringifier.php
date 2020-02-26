<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class PriceCollectionValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function stringify(ValueInterface $value, array $options = []): string
    {
        Assert::isInstanceOf($value, PriceCollectionValueInterface::class);

        if (array_key_exists('currency', $options)) {
            $price = $value->getPrice($options['currency']);
            if (null === $price) {
                return '';
            }

            return sprintf('%s %s', $this->formatNumber($price->getData()), $price->getCurrency());
        }

        $priceStrings = [];
        foreach ($value->getData() as $price) {
            if (null !== $price->getData()) {
                $priceStrings[] = sprintf('%s %s', $this->formatNumber($price->getData()), $price->getCurrency());
            }
        }

        return implode(', ', $priceStrings);
    }

    private function formatNumber($number): string
    {
        $formattedPrice = sprintf('%.2F', $number);

        return str_replace('.00', '', $formattedPrice);
    }
}
