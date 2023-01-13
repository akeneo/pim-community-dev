<?php

namespace Akeneo\Catalogs\Infrastructure\Service\Mapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeOptionsByCodeQueryInterface;

class AttributeSimpleselectService extends AbstractAttributeService implements AttributeServiceInterface
{

    public function __construct(
        private readonly GetAttributeOptionsByCodeQueryInterface $getAttributeOptionsByCodeQuery,
    ) {
    }

    public function execute(array $product, array $source): ?string
    {
        $sourceValue = $this->getProductAttributeValue(
            $product,
            $source['source'],
            $source['locale'],
            $source['scope']
        );

        $locale = $source['locale'] ?? 'en_US';
        return $this->getSimpleSelectLabel($source['source'], $sourceValue, $locale);
    }

    private function getSimpleSelectLabel(string $attributeCode, $optionCode, string $locale): string | null
    {
        $options = $this->getAttributeOptionsByCodeQuery->execute($attributeCode, [$optionCode], $locale);

        if (!empty($options)) {
            return $options[0]['label'];
        } else {
            return null;
        }
    }
}
