<?php

namespace Akeneo\Catalogs\Infrastructure\Service\Mapping;

use Akeneo\Catalogs\Application\Service\MapAttributeServiceInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class MapAttributeService implements MapAttributeServiceInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $locator
    ) {
    }

    public static function getSubscribedServices()
    {
        return [
            'pim_catalog_text' => AttributeTextService::class,
            'pim_catalog_simpleselect' => AttributeSimpleselectService::class,
        ];
    }

    public function execute(string $attributeType, array $product, array $source){
        if ($this->locator->has($attributeType)) {
            $attributeService = $this->locator->get($attributeType);

            return $attributeService->execute($product, $source);
        }
        // throw
    }
}
