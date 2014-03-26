<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Updates product document when an entity related to product is removed
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRelatedEntityRemovalSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $entityMapping = [
        'Pim\Bundle\CatalogBundle\Model\Association'       => 'Association',
        'Pim\Bundle\CatalogBundle\Model\AbstractAttribute' => 'Attribute',
        'Pim\Bundle\CatalogBundle\Entity\AttributeOption'  => 'AttributeOption',
        'Pim\Bundle\CatalogBundle\Model\CategoryInterface' => 'Category',
        'Pim\Bundle\CatalogBundle\Entity\Family'           => 'Family',
        'Pim\Bundle\CatalogBundle\Entity\Group'            => 'Group',
        'Pim\Bundle\CatalogBundle\Entity\Channel'          => 'Channel',
    ];

    /**
     * Pending batch product document updates
     *
     * @var array
     */
    protected $pendingUpdates = array();

    /**
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $registry, $productClass)
    {
        $this->registry     = $registry;
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

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->addPendingUpdates($entity);
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->executePendingUpdates();
    }

    /**
     * Store pending batch updates
     *
     * @param object $entity
     */
    protected function addPendingUpdates($entity)
    {
        foreach ($this->entityMapping as $class => $name) {
            if ($entity instanceof $class) {
                $method = sprintf('cascade%sRemoval', $name);
                $this->pendingUpdates[$method][] = $entity->getId();
            }
        }
    }

    /**
     * Execute pending batch updates
     */
    protected function executePendingUpdates()
    {
        $repository = $this->registry->getRepository($this->productClass);

        foreach ($this->pendingUpdates as $method => $ids) {
            if (method_exists($repository, $method)) {
                foreach ($ids as $id) {
                    $repository->$method($id);
                }
            }
        }

        $this->pendingUpdates = array();
    }
}
