<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeHandler
{
    /** @var AttributeFactoryRegistryInterface */
    private $attributeFactoryRegistry;

    public function __construct(
        AttributeFactoryRegistryInterface $attributeFactoryRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactoryRegistry = $attributeFactoryRegistry;
    }

    public function __invoke(AbstractCreateAttributeCommand $command): void
    {
        $attribute = $this->attributeFactoryRegistry->getFactory($command)->create($command);
        $this->attributeRepository->create($attribute);
    }
}
