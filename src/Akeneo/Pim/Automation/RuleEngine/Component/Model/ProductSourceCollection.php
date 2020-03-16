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

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductSourceCollection implements \IteratorAggregate
{
    /** @var ProductSource[] */
    private $productSources = [];

    private function __construct(array $productSources)
    {
        Assert::greaterThanEq(count($productSources), 2, 'At least two sources must be defined.');
        Assert::allIsInstanceOf($productSources, ProductSource::class);

        $this->productSources = $productSources;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->productSources);
    }

    public static function fromNormalized(array $normalized): self
    {
        return new self(array_map(function (array $normalizedSource): ProductSource {
            return ProductSource::fromNormalized($normalizedSource);
        }, $normalized));
    }
}
