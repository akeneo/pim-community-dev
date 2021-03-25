<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Exception\AttributeAsLabelException;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Driver\Connection;
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
    ) {
        $this->dbConnection = $dbConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'preRemove',
        ];
    }

    /**
     * Check if the attribute is used as label by any family
     *
     * @param RemoveEvent $event
     * @throws AttributeAsLabelException
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

        $families = $this->getFamiliesWhoseAttributeAsLabelIsTheSameAsTheOneAffectedByTheDeletion($event);

        foreach($families as $family) {
            if ($attribute->getId() === $family->getAttributeAsLabel()) {
                $message = sprintf(
                    'Attributes used as labels in a family cannot be removed.'
                );

                throw new AttributeAsLabelException($message);
            }
        }
    }

    /**
     * Check if the attributes are used as label by any family
     *
     * @param RemoveEvent $event
     * @throws AttributeAsLabelException
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

        $families = $this->getFamiliesWhoseAttributeAsLabelIsTheSameAsTheOneAffectedByTheDeletion($event);

        foreach ($attributes as $attribute) {
            foreach($families as $family) {
                if ($attribute->getId() === $family->getAttributeAsLabel()) {
                    $message = sprintf(
                        'Attributes used as labels in a family cannot be removed.'
                    );

                    throw new AttributeAsLabelException($message);
                }
            }
        }
    }


        private function getFamiliesWhoseAttributeAsLabelIsTheSameAsTheOneAffectedByTheDeletion(RemoveEvent $event): array
    {

        $attribute = $event->getSubject();
        $sql = <<<SQL
SELECT id 
FROM pim_catalog_family
WHERE label_attribute_id = :attribute_id;
SQL;

        $families = $this->dbConnection->executeQuery(
            $sql,
            [
                'attribute_id' => $attribute->getId()
            ]
        );

        return $families;
    }
}
