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

interface ValueStringifierInterface
{
    /**
     * Returns the attribute types supported by the stringifier.
     *
     * @return string[]
     */
    public function forAttributesTypes(): array;

    /**
     * Converts the value in string.
     */
    public function stringify(ValueInterface $value, array $options = []): string;
}
