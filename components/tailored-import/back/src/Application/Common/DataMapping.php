<?php

namespace Akeneo\Platform\TailoredImport\Application\Common;

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
            AttributeTarget::TYPE => AttributeTarget::createFromNormalized($normalizedTarget),
            PropertyTarget::TYPE => PropertyTarget::createFromNormalized($normalizedTarget),
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