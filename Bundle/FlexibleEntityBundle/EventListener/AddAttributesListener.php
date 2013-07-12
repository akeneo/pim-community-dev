<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * Aims to inject available attributes into a flexible entity
 *
 */
class AddAttributesListener implements EventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad'
        );
    }

    /**
     * After load
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $flexible = $args->getEntity();
        $em       = $args->getEntityManager();

        if ($flexible instanceof AbstractEntityFlexible) {

            $metadata               = $em->getMetadataFactory()->getLoadedMetadata();
            $flexibleMetadata       = $metadata[get_class($flexible)];
            $flexibleAssociations   = $flexibleMetadata->getAssociationMappings();
            $toValueAssociation     = $flexibleAssociations['values'];
            $valueClass             = $toValueAssociation['targetEntity'];

            $valueMetadata          = $metadata[$valueClass];
            $valueAssociations      = $valueMetadata->getAssociationMappings();
            $toAttributeAssociation = $valueAssociations['attribute'];
            $attributeClass         = $toAttributeAssociation['targetEntity'];

            $codeToAttributeData = $em->getRepository($attributeClass)->getCodeToAttributes(get_class($flexible));
            $flexible->setAllAttributes($codeToAttributeData);
            $flexible->setValueClass($valueClass);
        }
    }
}
