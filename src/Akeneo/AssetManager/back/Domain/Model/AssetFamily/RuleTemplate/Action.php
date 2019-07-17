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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Field;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Item;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\ItemCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Items;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Type;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Action
{
    /** @var Field */
    private $field;

    /** @var Type */
    private $type;

    /** @var ItemCollection */
    private $items;

    /** @var ChannelReference */
    private $channel;

    /** @var LocaleReference */
    private $locale;

    private function __construct(Field $field, Type $type, ItemCollection $items, ChannelReference $channel, LocaleReference $locale)
    {
        $this->field   = $field;
        $this->type    = $type;
        $this->items   = $items;
        $this->channel = $channel;
        $this->locale  = $locale;
    }

    public static function createFromNormalized(array $action): self
    {
        $field = Field::createFromNormalized($action['field']);
        $type = Type::createFromNormalized($action['type']);
        $items = ItemCollection::createFromNormalized($action['items']);
        $channel = ChannelReference::createFromNormalized($action['channel']);
        $locale = LocaleReference::createFromNormalized($action['locale']);

        return new self($field, $type, $items, $channel, $locale);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): self
    {
        $field = ReplacePatterns::replace($this->field->stringValue(), $propertyAccessibleAsset);
        $items = array_map(function (Item $item) use ($propertyAccessibleAsset) {
            return ReplacePatterns::replace($item->stringValue(), $propertyAccessibleAsset);
        }, $this->items->normalize());

        return new self(
            Field::createFromNormalized($field),
            $this->type,
            ItemCollection::createFromNormalized($items),
            $this->channel,
            $this->locale
        );
    }

    public function normalize(): array
    {
        return [
            'field' => $this->field->stringValue(),
            'type' => $this->type->stringValue(),
            'items' => $this->items->stringValue(),
            'channel' => $this->channel->normalize(),
            'locale' => $this->locale->normalize(),
        ];
    }
}
