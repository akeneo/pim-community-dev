<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\Target\SourceParameter\SourceParameterInterface;
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
        private string $actionIfNotEmpty,
        private string $actionIfEmpty,
        private ?SourceParameterInterface $sourceParameter,
    ) {
        Assert::stringNotEmpty($this->code);
        Assert::inArray($this->actionIfNotEmpty, [TargetInterface::ACTION_ADD, TargetInterface::ACTION_SET]);
        Assert::inArray($this->actionIfEmpty, [TargetInterface::IF_EMPTY_CLEAR, TargetInterface::IF_EMPTY_SKIP]);
    }

    public static function create(
        string $code,
        string $actionIfNotEmpty,
        string $actionIfEmpty,
        ?SourceParameterInterface $sourceParameter,
    ): self {
        return new self($code, $actionIfNotEmpty, $actionIfEmpty, $sourceParameter);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getActionIfNotEmpty(): string
    {
        return $this->actionIfNotEmpty;
    }

    public function getActionIfEmpty(): string
    {
        return $this->actionIfEmpty;
    }

    public function getSourceParameter(): ?SourceParameterInterface
    {
        return $this->sourceParameter;
    }
}
