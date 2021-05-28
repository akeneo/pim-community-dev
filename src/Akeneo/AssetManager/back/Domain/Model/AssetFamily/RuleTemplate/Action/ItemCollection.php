<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;

use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ItemCollection
{
    private array $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function createFromNormalized(array $normalizedItems): self
    {
        Assert::allStringNotEmpty($normalizedItems, 'All the item values should be a string not empty');

        $items = [];
        foreach ($normalizedItems as $item) {
            $items[] = Item::createFromNormalized($item);
        }
        return new self($items);
    }

    public function normalize(): array
    {
        $normalizedItems = [];
        /** @var Item $item */
        foreach ($this->items as $item) {
            $normalizedItems[] = $item->stringValue();
        }

        return $normalizedItems;
    }
}
