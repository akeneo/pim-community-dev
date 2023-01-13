<?php

namespace Akeneo\Catalogs\Infrastructure\Service\Mapping;

interface AttributeServiceInterface
{
    public function execute(array $product, array $source);
}
