<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Exception\AttributeRemovalException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Forbid deletion if the attribute is used as label by any family
 *
 */
class CheckAttributeOnDeletionSubscriber implements EventSubscriberInterface
{
    private Connection $dbConnection;

    public function __construct(
        Connection $dbConnection
    )
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'preRemove',
            StorageEvents::PRE_REMOVE_ALL => 'bulkPreRemove',
        ];
    }

    /**
     * Check if the attribute is used as label by any family
     *
     * @param RemoveEvent $event
     * @throws AttributeRemovalException
     */
    public function preRemove(RemoveEvent $event)
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }
        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }

        if ($this->areAttributesUsedAsLabelInAFamily([$attribute->getId()])) {
            throw new AttributeRemovalException('pim_enrich.entity.attribute.flash.update.cant_remove_attributes_used_as_label');
        }
    }

    /**
     * Check if the attributes are used as label by any family
     *
     * @param RemoveEvent $event
     * @throws AttributeRemovalException
     */
    public function bulkPreRemove(RemoveEvent $event)
    {
        $attributes = $event->getSubject();

        if (!is_array($attributes)) {
            return;
        }
        $attributes = array_filter(
            $attributes,
            fn($attr): bool => $attr instanceof AttributeInterface
        );
        if ([] === $attributes) {
            return;
        }

        $attributeIds = array_map(
            fn(AttributeInterface $attr):int => $attr->getId(),
            $attributes
        );

        if ($this->areAttributesUsedAsLabelInAFamily($attributeIds)) {
            throw new AttributeRemovalException('pim_enrich.entity.attribute.flash.update.cant_remove_attributes_used_as_label');
        }
    }

    private function areAttributesUsedAsLabelInAFamily(array $attributeIds): bool
    {
        $sql = <<<SQL
SELECT EXISTS(
    SELECT * FROM pim_catalog_family
    WHERE label_attribute_id IN (:attributeIds)
)
SQL;

        $result = $this->dbConnection->executeQuery(
            $sql,
            [
                'attributeIds' => $attributeIds
            ],
            [
                'attributeIds' => Connection::PARAM_INT_ARRAY
            ]
        )->fetchColumn();

        return (bool)$result;
    }
}
