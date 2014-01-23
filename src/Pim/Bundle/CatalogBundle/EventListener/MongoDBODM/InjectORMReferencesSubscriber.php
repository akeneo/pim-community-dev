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
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param string        $attributeClass
     */
    public function __construct(EntityManager $entityManager, $attributeClass)
    {
        $this->entityManager = $entityManager;
        $this->attributeClass = $attributeClass;
    }

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
        $valueMetadata = $documentManager->getClassMetadata(get_class($value));

        $attributeReflProp = $valueMetadata->reflClass->getProperty('attribute');
        $attributeReflProp->setAccessible(true);

        $attributeReflProp->setValue(
            $value,
            $this->entityManager->getReference($this->attributeClass, $value->getAttributeId())
        );
    }

    /**
     * Add the reference to family inside the value
     *
     * @param ProductInterface $product
     * @param DocumentManager  $documentManager
     */
    protected function setFamilyReference(ProductInterface $product, DocumentManager $documentManager)
    {
        if (null === $product->getFamilyId()) {
            return;
        }

        $productMetadata = $documentManager->getClassMetadata(get_class($product));

        $familyReflProp = $productMetadata->reflClass->getProperty('family');
        $familyReflProp->setAccessible(true);

        $familyReflProp->setValue(
            $product,
            $this->entityManager->getReference('PimCatalogBundle:Family', $product->getFamilyId())
        );
    }
}
