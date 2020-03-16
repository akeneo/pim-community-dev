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
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class DateValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    /**
     * {@inheritDoc}
     */
    public function stringify(ValueInterface $value, array $options = []): string
    {
        Assert::isInstanceOf($value, DateValueInterface::class);

        $date = $value->getData();
        if (!$date instanceof \DateTime) {
            return '';
        }

        if (array_key_exists('format', $options)) {
            return $date->format($options['format']);
        }

        return $value->__toString();
    }
}
