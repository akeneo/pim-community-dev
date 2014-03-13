<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Set the normalized data of a Product document
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetProductNormalizedDataSubscriber implements EventSubscriber
{
    /**
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['prePersist', 'preUpdate'];
    }

    /**
     * Set product normalized data before inserting it
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof ProductInterface) {
            return;
        }

        $this->updateNormalizedData($document);
    }

    /**
     * Set product normalized data before updating it
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof ProductInterface) {
            return;
        }

        $this->updateNormalizedData($document);

        $dm = $args->getDocumentManager();
        $class = $dm->getClassMetadata(get_class($document));
        $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
    }

    /**
     * Update product normalized data
     *
     * @param LifecycleEventArgs $args
     */
    protected function updateNormalizedData(ProductInterface $product)
    {
        $product->setNormalizedData(
            $this->normalizer->normalize($product, 'bson')
        );
    }
}
