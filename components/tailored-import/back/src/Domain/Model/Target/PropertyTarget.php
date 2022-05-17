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

final class PropertyTarget implements TargetInterface
{
    public const TYPE = 'property';

    private function __construct(
        private string $code,
        private string $actionIfNotEmpty,
        private string $actionIfEmpty,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::inArray($this->actionIfNotEmpty, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->actionIfEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function create(
        string $code,
        string $actionIfNotEmpty,
        string $actionIfEmpty,
    ): self {
        return new self($code, $actionIfNotEmpty, $actionIfEmpty);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getActionIfNotEmpty(): string
    {
        return $this->actionIfNotEmpty;
    }

    public function getActionIfEmpty(): string
    {
        return $this->actionIfEmpty;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'type' => self::TYPE,
            'action_if_not_empty' => $this->actionIfNotEmpty,
            'action_if_empty' => $this->actionIfEmpty,
        ];
    }
}
