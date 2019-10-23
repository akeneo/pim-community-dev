<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Load real entity values object from the $rawValues field (ie: values in JSON)
 * when an entity with values is loaded by Doctrine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: we could use an Entity Listener instead (need to upgrade bundle to 1.3)
 * TODO: cf. http://symfony.com/doc/current/bundles/DoctrineBundle/entity-listeners.html
 * TODO: cf. http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#entity-listeners
 */
final class LoadEntityWithValuesSubscriber implements EventSubscriber
{
    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    public function __construct(WriteValueCollectionFactory $valueCollectionFactory)
    {
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }

    /**
     * Here we load the real object values from the raw values field.
     *
     * For products, we also add the identifier as a regular value
     * so that it can be used in the product edit form transparently.
     *
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithValuesInterface) {
            return;
        }

        $rawValues = $entity->getRawValues();

        $values = $this->valueCollectionFactory->createFromStorageFormat($rawValues);
        $entity->setValues($values);
    }
}
