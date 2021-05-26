<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Query\Attribute;

use Webmozart\Assert\Assert;

/**
 * For an attribute, there could be a multiple combination of value keys,
 * depending on Channel & Locale (if it has multiple values per channel and/or locale).
 *
 * For instance, let's have the attribute "name" for the asset family "brand".
 * It has distinct values per channel, for "ecommerce" and "mobile".
 * It has distinct values per locale, for "fr_FR" and "en_US".
 *
 * So it will have the following value keys:
 *   - One for: name, brand, ecommerce, fr_FR
 *   - One for: name, brand, ecommerce, en_US
 *   - One for: name, brand, mobile, fr_FR
 *   - One for: name, brand, mobile, en_US
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ValueKeyCollection implements \IteratorAggregate
{
    /** @var ValueKey[] */
    private array $valueKeys;

    private function __construct(array $valueKeys)
    {
        $this->valueKeys = $valueKeys;
    }

    public static function fromValueKeys(array $valueKeys): self
    {
        Assert::allIsInstanceOf(
            $valueKeys,
            ValueKey::class,
            sprintf('All value keys should be an instance of "%s"', ValueKey::class)
        );

        return new self($valueKeys);
    }

    public function normalize(): array
    {
        return array_map(fn (ValueKey $valueKey) => $valueKey->__toString(), $this->valueKeys);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->valueKeys);
    }
}
