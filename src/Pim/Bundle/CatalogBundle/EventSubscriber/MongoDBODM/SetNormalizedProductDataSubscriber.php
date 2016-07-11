<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Set the normalized data of a Product document. These normalized data
 * are stored in the product along the "normal" data structure.
 * They are used for filtering and sorting, as the "normal" data structure
 * cannot be used for that ($elemMatch work for filtering, but no way to
 * sort by a embeddedDocument in an array
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetNormalizedProductDataSubscriber implements EventSubscriber
{
    /** @var NormalizerInterface */
    protected $normalizer;

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
        if ($document instanceof ProductInterface) {
            $this->updateNormalizedData($document);
        }
    }

    /**
     * Set product normalized data before updating it
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof ProductInterface) {
            $this->updateNormalizedData($document);

            $dm = $args->getDocumentManager();
            $metadata = $dm->getClassMetadata(get_class($document));
            $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($metadata, $document);
        }
    }

    /**
     * Update product normalized data
     *
     * @param ProductInterface $product
     */
    protected function updateNormalizedData(ProductInterface $product)
    {
        $product->setNormalizedData(
            $this->normalizer->normalize($product, 'mongodb_json')
        );
    }
}
