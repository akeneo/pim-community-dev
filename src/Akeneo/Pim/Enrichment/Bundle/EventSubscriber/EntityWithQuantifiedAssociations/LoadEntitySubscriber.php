<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
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
final class LoadEntitySubscriber implements EventSubscriber
{
  /** @var GetProductIdentifiersFromProductIdsQueryInterface */
  private $getIdMappingFromProductIds;

  /** @var GetProductModelCodesFromProductModelIdsQueryInterface */
  private $getIdMappingFromProductModelIds;

    public function __construct(
      GetIdMappingFromProductIdsQueryInterface $getIdMappingFromProductIds,
      GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds
    ) {
        $this->getIdMappingFromProductIds = $getIdMappingFromProductIds;
        $this->getIdMappingFromProductModelIds = $getIdMappingFromProductModelIds;
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
        if (!$entity instanceof EntityWithQuantifiedAssociationsInterface) {
            return;
        }
        $productIds = $entity->getQuantifiedAssociationsProductIds();
        $productModelIds = $entity->getQuantifiedAssociationsProductModelIds();

        $mappedProductIds = $this->getIdMappingFromProductIds->execute($productIds);
        $mappedProductModelIds = $this->getIdMappingFromProductModelIds->execute($productModelIds);

        $entity->hydrateQuantifiedAssociations($mappedProductIds, $mappedProductModelIds);
    }
}
