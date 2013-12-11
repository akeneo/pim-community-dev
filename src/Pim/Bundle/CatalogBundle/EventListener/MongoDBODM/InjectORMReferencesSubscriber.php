<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\EventSubscriber;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Inject ORM attribute object into ProductValue loaded from MongoDB
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InjectORMReferencesSubscriber implements EventSubscriber
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
     * After load, adds ORM references to document
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $documentManager = $args->getDocumentManager();

        if ($document instanceof ProductValueInterface) {
            $this->setAttributeReference($document, $documentManager);
        } elseif ($document instanceof ProductInterface) {
            $this->setFamilyReference($document, $documentManager);
        }
    }

    /**
     * Add the reference to attribute inside the value
     * in order to be able to lazyload it when needed
     *
     * @param ProductValueInterface $value
     * @param DocumentManager       $documentManager
     */
    protected function setAttributeReference(ProductValueInterface $value, DocumentManager $documentManager)
    {
        // FIXME_MONGO: get the productValue classname from ProductManager (can be something else)
        $valueMetadata = $documentManager->getClassMetadata('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $attributeReflProp = $valueMetadata->reflClass->getProperty('attribute');
        $attributeReflProp->setAccessible(true);

        $attributeReflProp->setValue(
            $value,
            $this->entityManager->getReference('PimCatalogBundle:ProductAttribute', $value->getAttributeId())
        );
    }

    /**
     * Add the reference to family inside the product
     *
     * @param ProductInterface $product
     * @param DocumentManager  $documentManager
     */
    protected function setFamilyReference(ProductInterface $product, DocumentManager $documentManager)
    {
        // FIXME_MONGO: get the productValue classname from ProductManager (can be something else)
        $productMetadata = $documentManager->getClassMetadata('Pim\Bundle\CatalogBundle\Model\Product');

        $familyReflProp = $productMetadata->reflClass->getProperty('family');
        $familyReflProp->setAccessible(true);

        $familyReflProp->setValue(
            $product,
            $this->entityManager->getReference('PimCatalogBundle:Family', $product->getFamilyId())
        );
    }
}
