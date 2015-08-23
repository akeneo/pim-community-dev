<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeRequirementInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Ensure that all attribute requirements are displayed for a family
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeRequirementsSubscriber implements EventSubscriberInterface
{
    /**
     * @var ChannelInterface[]
     */
    protected $channels;

    /**
     * @param ChannelManager $channelManager
     */
    public function __construct(ChannelManager $channelManager)
    {
        $this->channels = $channelManager->getChannels();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
        );
    }

    /**
     * Merge missing attribute requirements to existing one
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $family = $event->getData();

        if (null === $family || !$family instanceof FamilyInterface) {
            return;
        }

        $requirements = array();

        foreach ($family->getAttributes() as $attribute) {
            foreach ($this->channels as $channel) {
                $requirement = $this->createAttributeRequirement($channel, $attribute, $family);

                $key = $family->getAttributeRequirementKey($requirement);
                $requirements[$key] = $requirement;
            }
        }

        $requirements = array_merge($requirements, $family->getAttributeRequirements());

        $family->setAttributeRequirements($requirements);
    }

    /**
     * Remove identifier attributes from form fields and make sure they are always required
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $family = $event->getData();

        if (null === $family || !$family instanceof FamilyInterface) {
            return;
        }

        $form = $event->getForm();

        foreach ($family->getAttributeRequirements() as $key => $requirement) {
            if (AttributeTypes::IDENTIFIER === $requirement->getAttribute()->getAttributeType()) {
                $requirement->setRequired(true);
                $form->get('attributeRequirements')->remove($key);
            }
        }
    }

    /**
     * Create attribute requirement entity
     *
     * @param ChannelInterface   $channel
     * @param AttributeInterface $attribute
     * @param FamilyInterface    $family
     *
     * @return AttributeRequirementInterface
     */
    protected function createAttributeRequirement(
        ChannelInterface $channel,
        AttributeInterface $attribute,
        FamilyInterface $family
    ) {
        $requirement = new AttributeRequirement();
        $requirement->setChannel($channel);
        $requirement->setAttribute($attribute);
        $requirement->setFamily($family);

        return $requirement;
    }
}
