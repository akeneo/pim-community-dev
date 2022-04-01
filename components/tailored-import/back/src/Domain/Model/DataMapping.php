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

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Webmozart\Assert\Assert;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;

class DataMapping
{
    private function __construct(
        private string $uuid,
        private TargetInterface $target,
        private array $sources,
        private OperationCollection $operations,
        private array $sampleData,
    ) {
        Assert::uuid($uuid);
    }

    public static function create(
        string $uuid,
        TargetInterface $target,
        array $sources,
        OperationCollection $operations,
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

    public function getOperations(): OperationCollection
    {
        return $this->operations;
    }

    public function getSampleData(): array
    {
        return $this->sampleData;
    }
}
