<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Target;

use Webmozart\Assert\Assert;

class AttributeTarget implements TargetInterface
{
    public const TYPE = 'attribute';

    private function __construct(
        private string $code,
        private string $attributeType,
        private ?string $channel,
        private ?string $locale,
        private string $actionIfNotEmpty,
        private string $actionIfEmpty,
        private ?array $sourceConfiguration,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::stringNotEmpty($this->attributeType);
        Assert::notSame($this->channel, '');
        Assert::notSame($this->locale, '');
        Assert::inArray($this->actionIfNotEmpty, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->actionIfEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function create(
        string $code,
        string $attributeType,
        ?string $channel,
        ?string $locale,
        string $actionIfNotEmpty,
        string $actionIfEmpty,
        ?array $sourceConfiguration,
    ): self {
        return new self($code, $attributeType, $channel, $locale, $actionIfNotEmpty, $actionIfEmpty, $sourceConfiguration);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getAttributeType(): string
    {
        return $this->attributeType;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getActionIfNotEmpty(): string
    {
        return $this->actionIfNotEmpty;
    }

    public function getActionIfEmpty(): string
    {
        return $this->actionIfEmpty;
    }

    public function getSourceConfiguration(): ?array
    {
        return $this->sourceConfiguration;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'type' => self::TYPE,
            'attribute_type' => $this->attributeType,
            'channel' => $this->channel,
            'locale' => $this->locale,
            'action_if_not_empty' => $this->actionIfNotEmpty,
            'action_if_empty' => $this->actionIfEmpty,
            'source_configuration' => $this->sourceConfiguration,
        ];
    }
}
