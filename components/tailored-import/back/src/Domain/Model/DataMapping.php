<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataMapping
{
    private function __construct(
        private string $uuid,
        private TargetInterface $target,
        private array $sources,
        private array $operations,
        private array $sampleData,
    ) {
        Assert::uuid($uuid);
    }

    public static function create(
        string $uuid,
        TargetInterface $target,
        array $sources,
        array $operations,
        array $sampleData,
    ): self {
        return new self($uuid, $target, $sources, $operations, $sampleData);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getTarget(): TargetInterface
    {
        return $this->target;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getSampleData(): array
    {
        return $this->sampleData;
    }
}
