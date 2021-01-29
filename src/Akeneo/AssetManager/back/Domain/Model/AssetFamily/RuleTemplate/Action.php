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

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Field;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\ItemCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\Type;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Action
{
    public const ADD_MODE = 'add';
    public const REPLACE_MODE = 'replace';
    private const ITEM_PATTERN = '{{code}}';
    public const ALLOWED_MODES = [self::ADD_MODE, self::REPLACE_MODE];

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
        Assert::keyExists($action, 'field');
        Assert::keyExists($action, 'type');
        Assert::keyExists($action, 'items');

        $field = Field::createFromNormalized($action['field']);
        $type = Type::createFromNormalized($action['type']);
        $items = ItemCollection::createFromNormalized($action['items']);
        $channel = ChannelReference::createFromNormalized($action['channel'] ?? null);
        $locale = LocaleReference::createFromNormalized($action['locale'] ?? null);

        return new self($field, $type, $items, $channel, $locale);
    }

    public static function createFromProductLinkRule(array $action): self
    {
        $allowedModes = implode(', ', self::ALLOWED_MODES);
        Assert::oneOf($action['mode'], self::ALLOWED_MODES, sprintf('The action mode allowed should be one of these : "%s"', $allowedModes));

        $action['field'] = $action['attribute'];
        $action['type'] = self::REPLACE_MODE === $action['mode'] ? 'set' : 'add';
        $action['items'] = [self::ITEM_PATTERN];

        return self::createFromNormalized($action);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): self
    {
        $field = ReplacePattern::replace($this->field->stringValue(), $propertyAccessibleAsset);
        $items = array_map(function (string $item) use ($propertyAccessibleAsset) {
            return ReplacePattern::replace($item, $propertyAccessibleAsset);
        }, $this->items->normalize());

        $channelCompiled = $this->channel->normalize();
        if (!$this->channel->isEmpty()) {
            $channelCompiled = ReplacePattern::replace($this->channel->normalize(), $propertyAccessibleAsset);
        }

        $localeCompiled = $this->locale->normalize();
        if (!$this->locale->isEmpty()) {
            $localeCompiled = ReplacePattern::replace($this->locale->normalize(), $propertyAccessibleAsset);
        }

        return new self(
            Field::createFromNormalized($field),
            $this->type,
            ItemCollection::createFromNormalized($items),
            ChannelReference::createFromNormalized($channelCompiled),
            LocaleReference::createFromNormalized($localeCompiled)
        );
    }

    public function normalize(): array
    {
        return [
            'field'   => $this->field->stringValue(),
            'type'    => $this->type->stringValue(),
            'items'   => $this->items->normalize(),
            'channel' => $this->channel->normalize(),
            'locale'  => $this->locale->normalize(),
        ];
    }
}
