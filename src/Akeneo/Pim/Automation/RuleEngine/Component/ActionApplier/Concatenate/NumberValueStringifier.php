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

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
final class NumberValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    public function stringify(ValueInterface $value, array $options = []): string
    {
        $number = $value->__toString();
        if (strpos($number, '.') === false) {
            return $number;
        }

        return preg_replace('/\.?0*$/', '', $number);
    }
}
