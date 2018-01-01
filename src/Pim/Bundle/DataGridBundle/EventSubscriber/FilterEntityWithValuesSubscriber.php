<?php

namespace Pim\Bundle\DataGridBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Pim\Bundle\DataGridBundle\Entity\EntityWithFilteredValuesInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Filter values object from the $rawValues field (ie: values in JSON) when a product is loaded by Doctrine.
 * It is done for performance purpose, in order to not hydrate all the values as objects.
 *
 * This listener have to be executed before Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: use an Entity Listener instead, as for Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber
 */
class FilterEntityWithValuesSubscriber implements EventSubscriber
{
    /** @var AttributeInterface[] */
    protected $attributeCodesToFilter = [];

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
     *
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithFilteredValuesInterface) {
            return;
        }

        $rawValues = $entity->getRawValues();

        $filteredRawValues = [];
        foreach ($this->attributeCodesToFilter as $attributeCode) {
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
     * As it is a doctrine event, there is no way to pass a context (containing the attributes to filter),
     * so it's the only solution for now.
     *
     * @param AttributeInterface[] $attributeCodes
     */
    public function configureAttributeCodesToFilter(array $attributeCodes)
    {
        $this->attributeCodesToFilter = $attributeCodes;
    }
}
