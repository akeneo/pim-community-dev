<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Inject ORM attribute object into ProductValue loaded from MongoDB
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetValuesAttributesSubscriber implements EventSubscriber
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager; 
    }

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
     * After load, we add the reference to attribute inside the value
     * in order to be able to lazyload it when needed
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $value = $args->getDocument();

        if ($value instanceof ProductValueInterface) {
            $documentManager = $args->getDocumentManager();
            // FIXME_MONGO: get the productValue classname from ProductManager (can be something else)
            $valueMetadata = $documentManager->getClassMetadata('Pim\Bundle\CatalogBundle\Model\ProductValue');

            $attributeReflProp = $valueMetadata->reflClass->getProperty('attribute');
            $attributeReflProp->setAccessible(true);

            $attributeReflProp->setValue(
                $value,
                $this->entityManager->getReference('PimCatalogBundle:ProductAttribute', $value->getAttributeId())
            );
        }
    }
}
