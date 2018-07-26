<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsAttributeInterface;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeHandler
{
    /** @var AttributeFactoryRegistryInterface */
    private $attributeFactoryRegistry;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ExistsAttributeInterface */
    private $existsAttribute;

    public function __construct(
        AttributeFactoryRegistryInterface $attributeFactoryRegistry,
        AttributeRepositoryInterface $attributeRepository,
        ExistsAttributeInterface $existsAttribute
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactoryRegistry = $attributeFactoryRegistry;
        $this->existsAttribute = $existsAttribute;
    }

    /**
     * @throws \LogicException
     */
    public function __invoke(AbstractCreateAttributeCommand $command): void
    {
        $this->checkAttributeIsUnique($command);
        $this->checkAttributeOrderIsNotAlreadyTaken($command);
        $this->createAttribute($command);
    }

    /**
     * @throws \RuntimeException
     */
    private function checkAttributeIsUnique(AbstractCreateAttributeCommand $command): void
    {
        $attributeIdentifier = AttributeIdentifier::create(
            $command->identifier['enriched_entity_identifier'],
            $command->identifier['identifier']
        );
        if ($this->existsAttribute->withIdentifier($attributeIdentifier)) {
            throw new \RuntimeException(
                sprintf(
                    'Cannot create attribute with code "%s", for the enriched entity "%s" because it already exists',
                    $command->identifier['identifier'],
                    $command->identifier['enriched_entity_identifier']
                )
            );
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function checkAttributeOrderIsNotAlreadyTaken(AbstractCreateAttributeCommand $command): void
    {
        $attributeIdentifier = EnrichedEntityIdentifier::fromString($command->identifier['enriched_entity_identifier']);
        $order = AttributeOrder::fromInteger($command->order);

        if ($this->existsAttribute->withEnrichedEntityIdentifierAndOrder($attributeIdentifier, $order)) {
            throw new \RuntimeException(
                sprintf(
                    'There is already an attribute in the enriched entity "%s" for order %d',
                    $command->identifier['enriched_entity_identifier'],
                    $command->order
                )
            );
        }
    }

    private function createAttribute(AbstractCreateAttributeCommand $command): void
    {
        $factory = $this->attributeFactoryRegistry->getFactory($command);
        $attribute = $factory->create($command);
        $this->attributeRepository->create($attribute);
    }
}
