<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckAttributeIsNotUsedAsLabelOnDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'preRemove',
        ];
    }

    /**
     * @throws CannotRemoveAttributeException
     */
    public function preRemove(RemoveEvent $event)
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if ($this->attributeIsUsedAsLabel($attribute->getId())) {
            throw new CannotRemoveAttributeException('flash.attribute.cant_remove_attributes_used_as_label');
        }
    }

    private function attributeIsUsedAsLabel(int $attributeId): bool
    {
        $sql = <<<SQL
SELECT EXISTS(
    SELECT * FROM pim_catalog_family
    WHERE label_attribute_id = :attribute_id
)
SQL;

        return (bool) $this->connection->executeQuery($sql, ['attribute_id' => $attributeId])->fetchOne();
    }
}
