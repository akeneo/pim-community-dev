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
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition\Field;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition\Operator;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Condition\Value;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Webmozart\Assert\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Condition
{
    private Field $field;

    private Operator $operator;

    private Value $value;

    private ChannelReference $channel;

    private LocaleReference $locale;

    private function __construct(Field $field, Operator $operator, Value $value, ChannelReference $channel, LocaleReference $locale)
    {
        $this->field    = $field;
        $this->operator = $operator;
        $this->value    = $value;
        $this->channel  = $channel;
        $this->locale   = $locale;
    }

    public static function createFromNormalized(array $condition): self
    {
        Assert::keyExists($condition, 'field');
        Assert::keyExists($condition, 'operator');
        Assert::keyExists($condition, 'value');
        $field = Field::createFromNormalized($condition['field']);
        $operator = Operator::createFromNormalized($condition['operator']);
        $value = Value::createFromNormalized($condition['value']);
        $channel = ChannelReference::createFromNormalized($condition['channel'] ?? null);
        $locale = LocaleReference::createFromNormalized($condition['locale'] ?? null);

        return new self($field, $operator, $value, $channel, $locale);
    }

    public static function createFromProductLinkRule(array $condition): self
    {
        return self::createFromNormalized($condition);
    }

    public function compile(PropertyAccessibleAsset $propertyAccessibleAsset): self
    {
        $field = ReplacePattern::replace($this->field->stringValue(), $propertyAccessibleAsset);
        $value = ReplacePattern::replace($this->value->normalize(), $propertyAccessibleAsset);

        return new self(
            Field::createFromNormalized($field),
            $this->operator,
            Value::createFromNormalized($value),
            $this->channel,
            $this->locale
        );
    }

    public function normalize(): array
    {
        return [
            'field'    => $this->field->stringValue(),
            'operator' => $this->operator->stringValue(),
            'value'    => $this->value->normalize(),
            'channel'  => $this->channel->normalize(),
            'locale'   => $this->locale->normalize(),
        ];
    }
}
