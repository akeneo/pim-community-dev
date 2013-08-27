<?php

namespace Pim\Bundle\ProductBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Entity\AttributeRequirement;
use Pim\Bundle\ProductBundle\Entity\Family;

/**
 * Ensure that all attribute requirements are displayed for a family
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeRequirementsSubscriber implements EventSubscriberInterface
{
    protected $channels;
    protected $attributes;

    /**
     * @param array|ArrayCollection $channels
     * @param array|ArrayCollection $attributes
     */
    public function __construct($channels, $attributes)
    {
        $this->channels   = $channels;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    /**
     * Merge missing attribute requirements to existing one
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $family = $event->getData();

        if (null === $family || !$family instanceof Family) {
            return;
        }

        $requirements = array();

        foreach ($this->attributes as $attribute) {
            foreach ($this->channels as $channel) {
                $requirement = new AttributeRequirement();
                $requirement->setChannel($channel);
                $requirement->setAttribute($attribute);
                $requirement->setFamily($family);

                $key = $family->getAttributeRequirementKeyFor(
                    $attribute->getCode(),
                    $channel->getCode()
                );
                $requirements[$key] = $requirement;
            }
        }

        $requirements = new ArrayCollection(
            array_merge($requirements, $family->getAttributeRequirements())
        );

        $family->setAttributeRequirements($requirements);
    }
}
