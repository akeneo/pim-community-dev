<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Target;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceConfiguration\SourceConfigurationInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTarget implements TargetInterface
{
    const TYPE = 'attribute';

    private function __construct(
        private string $code,
        private string $type,
        private ?string $channel,
        private ?string $locale,
        private string $actionIfNotEmpty,
        private string $actionIfEmpty,
        private ?SourceConfigurationInterface $sourceConfiguration,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::stringNotEmpty($this->type);
        Assert::notSame($this->channel, '');
        Assert::notSame($this->locale, '');
        Assert::inArray($this->actionIfNotEmpty, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->actionIfEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function create(
        string $code,
        string $type,
        ?string $channel,
        ?string $locale,
        string $actionIfNotEmpty,
        string $actionIfEmpty,
        ?SourceConfigurationInterface $sourceConfiguration,
    ): self {
        return new self($code, $type, $channel, $locale, $actionIfNotEmpty, $actionIfEmpty, $sourceConfiguration);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return $this->type;
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

    public function getSourceConfiguration(): ?SourceConfigurationInterface
    {
        return $this->sourceConfiguration;
    }
}
