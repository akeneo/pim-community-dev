<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * Aims to inject available attributes into a flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
return true;
            $metadata               = $em->getMetadataFactory()->getLoadedMetadata();
            $flexibleMetadata       = $metadata[get_class($flexible)];
            $flexibleAssociations   = $flexibleMetadata->getAssociationMappings();
            $toValueAssociation     = $flexibleAssociations['values'];
            $valueClass             = $toValueAssociation['targetEntity'];

            $valueMetadata          = $metadata[$valueClass];
            $valueAssociations      = $valueMetadata->getAssociationMappings();
            $toAttributeAssociation = $valueAssociations['attribute'];
            $attributeClass         = $toAttributeAssociation['targetEntity'];

            $qb = $em->createQueryBuilder()->select('att')->from($attributeClass, 'att', 'att.code')
                ->where('att.entityType = :entityType')->setParameter('entityType', get_class($flexible));
            $codeToAttributeData = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_OBJECT);

            $flexible->setAllAttributes($codeToAttributeData);
            $flexible->setValueClass($valueClass);

            //var_dump($codeToAttributeData); exit();
/*
            if ($flexible instanceof Pim\Bundle\ProductBundle\Model\ProductInterface) {

                var_dump($codeToAttributeData); exit();

                var_dump($flexible->getAttributes());
                exit();
            }*/
        }
    }
}
