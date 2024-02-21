<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor;

/**
 * Goal of this class is to filter values in the standard format.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FilterValues
{
    private $channelCodeToKeep;

    private $localeCodesToKeep;

    private $attributeCodesToKeep;

    private function __construct(string $channelCodesToKeep, array $localeCodesToKeep, array $attributeCodesToKeep)
    {
        $this->channelCodeToKeep = $channelCodesToKeep;
        $this->localeCodesToKeep = $localeCodesToKeep;
        $this->attributeCodesToKeep = $attributeCodesToKeep;
    }

    public static function create(): self
    {
        return new self('', [], []);
    }

    public function filterByLocaleCodes(array $localeCodesToFilterOn): self
    {
        return new static($this->channelCodeToKeep, $localeCodesToFilterOn, $this->attributeCodesToKeep);
    }

    public function filterByChannelCode(string $channelCodeToFilterOn): self
    {
        return new static($channelCodeToFilterOn, $this->localeCodesToKeep, $this->attributeCodesToKeep);
    }

    public function filterByAttributeCodes(array $attributeCodesToKeep): self
    {
        return new static($this->channelCodeToKeep, $this->localeCodesToKeep, $attributeCodesToKeep);
    }

    public function execute(array $standardFormatValues): array
    {
        if ([] !== $this->attributeCodesToKeep) {
            $standardFormatValues = array_intersect_key($standardFormatValues, array_flip($this->attributeCodesToKeep));
        }

        foreach ($standardFormatValues as &$values) {
            if ([] !== $this->localeCodesToKeep) {
                $values = array_filter($values, function (array $value): bool {
                    return null === $value['locale'] || in_array($value['locale'], $this->localeCodesToKeep, true);
                });
            }

            if ('' !== $this->channelCodeToKeep) {
                $values = array_filter($values, function (array $value): bool {
                    return null === $value['scope'] || $value['scope'] === $this->channelCodeToKeep;
                });
            }

            $values = array_values($values);
        }

        $standardFormatValues = array_filter($standardFormatValues, function ($value) :bool {
            return [] !== $value;
        });

        return $standardFormatValues;
    }
}
