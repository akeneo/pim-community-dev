<?php

namespace Akeneo\Catalogs\Infrastructure\Service\Mapping;

class AttributeTextService extends AbstractAttributeService implements AttributeServiceInterface
{
    public function execute(array $product, array $source): ?string
    {
        return $this->getProductAttributeValue(
            $product,
            $source['source'],
            $source['locale'],
            $source['scope']
        );
    }
}
