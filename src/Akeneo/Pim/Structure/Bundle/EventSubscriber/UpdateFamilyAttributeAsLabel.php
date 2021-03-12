<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateFamilyAttributeAsLabel implements EventSubscriberInterface
{
    private Connection $dbConnection;
    private AttributeRepositoryInterface $attributeRepository;

    public function __construct(
        Connection $dbConnection,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->dbConnection = $dbConnection;
        $this->attributeRepository = $attributeRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'setIdentifierAsAttributeAsLabel',
            StorageEvents::POST_REMOVE_ALL => 'setBulkIdentifierAsAttributeAsLabel',
        ];
    }

    public function setIdentifierAsAttributeAsLabel(RemoveEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }
        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }
        $this->updateFamiliesWithoutAttributeAsLabel();
    }

    public function setBulkIdentifierAsAttributeAsLabel(RemoveEvent $event): void
    {
        $attributes = $event->getSubject();
        if (!is_array($attributes)) {
             return;
        }
        $attributes = array_filter(
            $attributes,
            fn ($attr): bool => $attr instanceof AttributeInterface
        );
        if ([] === $attributes) {
            return;
        }
        $this->updateFamiliesWithoutAttributeAsLabel();
    }

    private function updateFamiliesWithoutAttributeAsLabel(): void
    {
        $identifierAttribute = $this->attributeRepository->getIdentifier();

        $sql = <<<SQL
UPDATE pim_catalog_family
SET label_attribute_id = :identifier
WHERE label_attribute_id IS NULL;
SQL;
        $this->dbConnection->executeUpdate(
            $sql,
            [
                'identifier' => $identifierAttribute->getId()
            ]
        );
    }
}
