<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Aims to filter raw values field (JSON array) when an entity with values is loaded by Doctrine.
 *
 * This subscriber have to be executed before Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber.
 * It allows to increase drastically performance of the datagrid loading,
 * because it avoids to hydrate all the values of an entity.
 * Hydration is very costly when the number of values is important.
 *
 * WARNING: With this fix, we are partially loading products: it has to be done only for read purpose.
 * Saving an entity partially loaded could result in a loss of data.
 * This subscriber should be activated only for the datagrid.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: use an Entity Listener instead, as for Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber
 * TODO: refactor the loading of the datagrid to not use ProductInterface entity
 */
class FilterEntityWithValuesSubscriber implements EventSubscriber
{
    /** @var FilterEntityWithValuesSubscriberConfiguration */
    protected $configuration;

    public function __construct()
    {
        $this->configuration = FilterEntityWithValuesSubscriberConfiguration::doNotFilterEntityValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad
        ];
    }

    /**
     * Filter directly the real object values from the raw values field.
     * Should only be used in the datagrid context.
     *
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithValuesInterface || !$this->configuration->shouldFilterEntityValues()
        ) {
            return;
        }

        $attributeCodes = $this->configuration->attributeCodesToFilterEntityValues();
        if ($entity instanceof EntityWithFamilyInterface && null !== $entity->getFamily()) {
            $family = $entity->getFamily();
            if (null !== $family->getAttributeAsLabel()) {
                $attributeCodes[] = $family->getAttributeAsLabel()->getCode();
            }
            if (null !== $family->getAttributeAsImage()) {
                $attributeCodes[] = $family->getAttributeAsImage()->getCode();
            }
        }

        $rawValues = $entity->getRawValues();

        $filteredRawValues = [];
        foreach ($attributeCodes as $attributeCode) {
            if (isset($rawValues[$attributeCode])) {
                $filteredRawValues[$attributeCode] = $rawValues[$attributeCode];
            }
        }
        $entity->setRawValues($filteredRawValues);
    }

    /**
     * Configure attributes to keep in the raw values.
     * As it is a doctrine event, there is no way to pass a context (containing the attributes to filter).
     *
     * Therefore, this subscriber is stateful.
     *
     * @param FilterEntityWithValuesSubscriberConfiguration $configuration
     */
    public function configure(FilterEntityWithValuesSubscriberConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }
}
