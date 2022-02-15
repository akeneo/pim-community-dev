<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TargetProperty implements TargetInterface
{
    const TYPE = 'property';

    private function __construct(
        private string $code,
        private string $action,
        private string $ifEmpty,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::inArray($this->action, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->ifEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function createFromNormalized(array $normalizedPropertyTarget): self
    {
        return new self(
            $normalizedPropertyTarget['code'],
            $normalizedPropertyTarget['action'],
            $normalizedPropertyTarget['if_empty'],
        );
    }

    public function getCode(): string
    {
        return $this->code;
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
