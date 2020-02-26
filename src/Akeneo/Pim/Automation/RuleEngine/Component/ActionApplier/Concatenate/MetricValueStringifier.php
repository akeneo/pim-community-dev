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
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class MetricValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function stringify(ValueInterface $value, array $options = []): string
    {
        Assert::isInstanceOf($value, MetricValueInterface::class);
        if (null === $value->getAmount() || null === $value->getUnit()) {
            return '';
        }

        return sprintf('%s %s', $this->formatNumber($value->getAmount()), $value->getUnit());
    }

    /**
     * If number is an integer, should returns the number without decimal
     * If number is a float, should returns the number without ending 0.
     *
     * @param string $number
     * @return string
     */
    private function formatNumber(string $number): string
    {
        if (strpos($number, '.') === false) {
            return $number;
        }

        return rtrim($number, '.0');
    }
}
