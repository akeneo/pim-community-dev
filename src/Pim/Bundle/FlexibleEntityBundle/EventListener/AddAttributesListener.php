<?php

namespace Pim\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * Aims to inject available attributes into a flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return [
            'postLoad'
        ];
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

            $metadata             = $em->getMetadataFactory()->getLoadedMetadata();
            $entityClass          = ClassUtils::getRealClass(get_class($flexible));
            $flexibleMetadata     = $metadata[$entityClass];
            $flexibleAssociations = $flexibleMetadata->getAssociationMappings();
            $valueAssociation     = $flexibleAssociations['values'];
            $valueClass           = $valueAssociation['targetEntity'];

            $valueMetadata        = $metadata[$valueClass];
            $valueAssociations    = $valueMetadata->getAssociationMappings();
            $attributeAssociation = $valueAssociations['attribute'];
            $attributeClass       = $attributeAssociation['targetEntity'];

            $codeToAttributeData = $em->getRepository($attributeClass)->getCodeToAttributes($entityClass);
            $flexible->setAllAttributes($codeToAttributeData);
            $flexible->setValueClass($valueClass);
        }
    }
}
