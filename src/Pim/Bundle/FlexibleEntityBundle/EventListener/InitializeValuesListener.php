<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;
use Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent;
use Pim\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Aims to add all values / required values when create or load a new flexible :
 * - required : an empty (or default value) for each required attribute
 * - all : an empty (or default value) for each attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InitializeValuesListener implements EventSubscriberInterface
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FlexibleEntityEvents::CREATE_FLEXIBLE => ['onCreateFlexibleEntity'],
        ];
    }

    /**
     * Add values for each attribute
     * @param FilterFlexibleEvent $event
     */
    public function onCreateFlexibleEntity(FilterFlexibleEvent $event)
    {
        $flexible = $event->getEntity();
        $manager  = $event->getManager();

        if ($flexible instanceof FlexibleInterface and $manager->getFlexibleInitMode() !== 'empty') {
            $findBy = ['entityType' => $manager->getFlexibleName()];
            if ($manager->getFlexibleInitMode() === 'required_attributes') {
                $findBy['required'] = true;
            }

            $attributes = $manager->getAttributeRepository()->findBy($findBy);
            $this->addValues($manager, $flexible, $attributes);
        }
    }

    /**
     * @param FlexibleManager   $manager    the object manager
     * @param FlexibleInterface $flexible   the entity
     * @param array             $attributes the attributes
     */
    protected function addValues(FlexibleManager $manager, FlexibleInterface $flexible, $attributes)
    {
        foreach ($attributes as $attribute) {
            $value = $manager->createFlexibleValue();
            $value->setAttribute($attribute);
            if ($attribute->getDefaultValue() !== null) {
                $value->setData($attribute->getDefaultValue());
            }
            $flexible->addValue($value);
        }
    }
}
