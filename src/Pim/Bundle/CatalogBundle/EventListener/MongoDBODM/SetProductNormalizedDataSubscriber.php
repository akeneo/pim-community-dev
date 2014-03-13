<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
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
        return [Events::preUpdate];
    }

    /**
     * Set product normalized data before updating it
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof ProductInterface) {
            return;
        }

        $document->setNormalizedData(
            $this->normalizer->normalize($document, 'bson')
        );

        $dm = $args->getDocumentManager();
        $class = $dm->getClassMetadata(get_class($document));
        $dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
    }
}

