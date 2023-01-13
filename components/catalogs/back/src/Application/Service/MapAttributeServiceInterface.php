<?php

namespace Akeneo\Catalogs\Application\Service;

interface MapAttributeServiceInterface
{
    public function execute(string $attributeType, array $product, array $source);
}
