<?php

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Domain;

class ReplacementOperation implements Operation
{
    private array $mapping;

    private function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public static function createFromNormalized(array $normalizedOperation): self
    {
        return new self($normalizedOperation['mapping']);
    }

    public function hasMappedValue(string $value): bool
    {
        return array_key_exists($value, $this->mapping);
    }

    public function getMappedValue(string $value)
    {
        /** check after if we return null */
        if (!$this->hasMappedValue($value)) {
            return null;
        }

        return $this->mapping[$value];
    }
}