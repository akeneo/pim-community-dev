<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

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

    public function code(): string
    {
        return $this->code;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function ifEmpty(): string
    {
        return $this->ifEmpty;
    }
}
