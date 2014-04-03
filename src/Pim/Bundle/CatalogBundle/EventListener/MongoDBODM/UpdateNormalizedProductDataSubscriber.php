<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Sets the normalized data of a Product document when related entities are modified
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateNormalizedProductDataSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $entityMapping = [
        'Pim\Bundle\CatalogBundle\Model\AbstractAttribute' => 'Attribute',
        'Pim\Bundle\CatalogBundle\Entity\Family'           => 'Family',
        'Pim\Bundle\CatalogBundle\Entity\Channel'          => 'Channel',
    ];

    /**
     * Ids of documents to update
     *
     * @var string[]
     */
    protected $pendingProducts = array();

    /**
     * Ids of updated documents
     *
     * @var string[]
     */
    protected $updatedProducts = array();

    /**
     * @param ManagerRegistry     $registry
     * @param NormalizerInterface $normalizer
     * @param string              $productClass
     */
    public function __construct(ManagerRegistry $registry, NormalizerInterface $normalizer, $productClass)
    {
        $this->registry     = $registry;
        $this->normalizer   = $normalizer;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['onFlush', 'postFlush'];
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->scheduleRelatedProducts($entity);
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->scheduleRelatedProducts($entity);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $entity) {
            $this->scheduleRelatedProducts($entity);
        }

        foreach ($uow->getScheduledCollectionUpdates() as $entity) {
            $this->scheduleRelatedProducts($entity);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->processPendingProducts();
    }

    /**
     * Schedule products related to the entity for normalized data recalculation
     *
     * @param object $entity
     */
    protected function scheduleRelatedProducts($entity)
    {
        $productIds = $this->getRelatedProductIds($entity);
        foreach ($productIds as $id) {
            if (!in_array($id, $this->pendingProducts) && !in_array($id, $this->updatedProducts)) {
                $this->pendingProducts[] = $id;
            }
        }
    }

    /**
     * Find ids of products related to the entity
     *
     * @param object $entity
     *
     * @return array
     */
    protected function getRelatedProductIds($entity)
    {
        $repository = $this->registry->getRepository($this->productClass);

        foreach ($this->entityMapping as $class => $name) {
            if ($entity instanceof $class) {
                $method = sprintf('findAllIdsFor%s', $name);

                if (method_exists($repository, $method)) {
                    return $repository->$method($entity);
                }
            }
        }

        return [];
    }

    /**
     * Process products that are scheduled for normalized data recalculation
     */
    protected function processPendingProducts()
    {
        $manager = $this->registry->getManagerForClass($this->productClass);

        foreach ($this->pendingProducts as $productId) {
            $product = $manager->getRepository($this->productClass)->find($productId);
            if ($product) {
                $product->setNormalizedData($this->normalizer->normalize($product, 'mongodb_json'));
                $manager->persist($product);

                $this->updatedProducts[] = $productId;
            }
        }

        $this->pendingProducts = array();

        $updatedCount = count($this->updatedProducts);
        if ($updatedCount) {
            $manager->flush();
        }
    }
}
