<?php

namespace Pim\Bundle\DataGridBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Pim\Bundle\DataGridBundle\Entity\EntityWithFilteredValuesInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Filter values object from the $rawValues field (ie: values in JSON) when a product is loaded by Doctrine.
 * This subscriber have to be executed before Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber,
 * in order to not hydrate all the values as object.
 *
 * It is done for performance purpose for the datagrid. Therefore, entities are partially loaded.
 * It should only be used for read purpose, as it can lead to a loss of data if the entity is saved.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: use an Entity Listener instead, as for Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber
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
    public function getSubscribedEvents()
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
    public function postLoad(LifecycleEventArgs $event)
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
     * This method make the event subscriber stateful, which is not a good thing.
     *
     * As it is a doctrine event, there is no way to pass a context (containing the attributes to filter).
     *
     * @param FilterEntityWithValuesSubscriberConfiguration $configuration
     */
    public function configure(FilterEntityWithValuesSubscriberConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }
}
