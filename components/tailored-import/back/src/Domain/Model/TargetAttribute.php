<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetAttribute implements TargetInterface
{
    const TYPE = 'attribute';

    private function __construct(
        private string $code,
        private ?string $channel,
        private ?string $locale,
        private string $action,
        private string $ifEmpty,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::notSame($this->channel, '');
        Assert::notSame($this->locale, '');
        Assert::inArray($this->action, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->ifEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function createFromNormalized(array $normalizedAttributeTarget): self
    {
        return new self(
            $normalizedAttributeTarget['code'],
            $normalizedAttributeTarget['channel'],
            $normalizedAttributeTarget['locale'],
            $normalizedAttributeTarget['action'],
            $normalizedAttributeTarget['if_empty'],
        );
    }

    public function getCode(): string
    {
        return $this->code;
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
        return $this->action;
    }

    public function getActionIfEmpty(): string
    {
        return $this->ifEmpty;
    }
}
