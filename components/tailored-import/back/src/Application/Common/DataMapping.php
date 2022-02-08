<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

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
        private array $sampleData
    )
    {}

    public static function createFromNormalized(array $normalizedDataMapping)
    {
        return new self(
          $normalizedDataMapping['uuid'],
          self::createTarget($normalizedDataMapping['target']),
          $normalizedDataMapping['sources'],
          $normalizedDataMapping['operations'],
          $normalizedDataMapping['sample_data'],
        );
    }

    private static function createTarget(array $normalizedTarget): TargetInterface
    {
        return match ($normalizedTarget['type']) {
            TargetAttribute::TYPE => TargetAttribute::createFromNormalized($normalizedTarget),
            TargetProperty::TYPE => TargetProperty::createFromNormalized($normalizedTarget),
            default => throw new \RuntimeException(sprintf("unknow Target type provided: %s", $normalizedTarget['type'])),
        };
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