<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
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
        'Pim\Bundle\CatalogBundle\Model\Association'       => 'Association',
        'Pim\Bundle\CatalogBundle\Model\AbstractAttribute' => 'Attribute',
        'Pim\Bundle\CatalogBundle\Model\CategoryInterface' => 'Category',
        'Pim\Bundle\CatalogBundle\Entity\Family'           => 'Family',
        'Pim\Bundle\CatalogBundle\Entity\Group'            => 'Group',
    ];

    /**
     * Documents to update
     *
     * @var object[]
     */
    protected $pendingProducts = array();

    /**
     * @var integer[]
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
        return ['onFlush'];
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->scheduleRelatedProducts($entity);
        }

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

        $this->processPendingProducts();
    }

    /**
     * Schedule products related to the entity for normalized data recalculation
     *
     * @param object $entity
     */
    protected function scheduleRelatedProducts($entity)
    {
        $products = $this->getRelatedProducts($entity);
        foreach ($products as $product) {
            $oid = spl_object_hash($product);
            if (!isset($this->pendingProducts[$oid]) && !in_array($oid, $this->updatedProducts)) {
                $this->pendingProducts[$oid] = $product;
            }
        }
    }

    /**
     * Find products related to the entity
     *
     * @param object $entity
     *
     * @return array
     */
    protected function getRelatedProducts($entity)
    {
        $repository = $this->registry->getRepository($this->productClass);

        foreach ($this->entityMapping as $class => $name) {
            if ($entity instanceof $class) {
                $method = sprintf('findAllFor%s', $name);

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

        foreach ($this->pendingProducts as $document) {
            $document->setNormalizedData($this->normalizer->normalize($document, 'mongodb_json'));
            $manager->persist($document);

            $this->updatedProducts[] = spl_object_hash($document);
        }

        $this->pendingProducts = array();

        $updatedCount = count($this->updatedProducts);
        if ($updatedCount) {
            $manager->flush();
        }
    }
}
