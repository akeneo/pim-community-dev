<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\MongoDB\Collection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexCreator;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\IndexPurger;

/**
 * Makes sure that the right indexes are set on MongoDB.
 * WARNING: MongoDB allows only 64 indexes on a collection.
 * So we indexed only the filterable attribute and not the sortable-only ones.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnsureIndexesSubscriber implements EventSubscriber
{
    /** @var IndexCreator */
    protected $indexCreator;

    /** @var IndexPurger */
    protected $indexPurger;

    /**
     * @param IndexCreator $indexCreator
     * @param IndexPurger  $indexCreator
     */
    public function __construct(IndexCreator $indexCreator, IndexPurger $indexPurger)
    {
        $this->indexCreator = $indexCreator;
        $this->indexPurger  = $indexPurger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['postPersist', 'postUpdate', 'postRemove'];
    }

    /**
     * Executed at post insert time
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->ensureIndexesFromEntity($entity);
    }

    /**
     * Executed at post update time
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->ensureIndexesFromEntity($entity);
    }

    /**
     * Executed at post remove time
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->purgeIndexesFromEntity($entity);
    }

    /**
     * Ensure indexes from entity
     *
     * @param object $entity
     */
    protected function ensureIndexesFromEntity($entity)
    {
        if ($entity instanceof AbstractAttribute) {
            $this->ensureIndexesFromAttribute($entity);
        }

        if ($entity instanceof Channel) {
            $this->indexCreator->ensureIndexesFromChannel($entity);
        }

        if ($entity instanceof Locale) {
            if (true === $entity->isActivated()) {
                $this->indexCreator->ensureIndexesFromLocale($entity);
            } else {
                $this->indexPurger->purgeIndexesFromLocale($entity);
            }
        }

        if ($entity instanceof Currency) {
            if (true === $entity->isActivated()) {
                $this->indexCreator->ensureIndexesFromCurrency($entity);
            } else {
                $this->indexPurger->purgeIndexesFromCurrency($entity);
            }
        }
    }

    /**
     * Ensure indexes from attribute if needed
     *
     * @param AbstractAttribute $attribute
     */
    public function ensureIndexesFromAttribute(AbstractAttribute $attribute)
    {
        if ($attribute->isUseableAsGridFilter()
            || AbstractProduct::IDENTIFIER_TYPE === $attribute->getAttributeType()
            || $attribute->isUnique()) {
            $this->indexCreator->ensureIndexesFromAttribute($attribute);
        }
    }

    /**
     * Purge indexes from entity removal
     *
     * @param object $entity
     */
    protected function purgeIndexesFromEntity($entity)
    {
        if ($entity instanceof AbstractAttribute) {
            $this->indexPurger->purgeIndexesFromAttribute($entity);
        }

        if ($entity instanceof Channel) {
            $this->indexPurger->purgeIndexesFromChannel($entity);
        }

    }
}
