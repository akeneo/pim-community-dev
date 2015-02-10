<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product value update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAction($action)
    {
        return in_array(
            $action,
            [UpdateGuesserInterface::ACTION_UPDATE_ENTITY, UpdateGuesserInterface::ACTION_DELETE]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        if (UpdateGuesserInterface::ACTION_DELETE === $action) {
            return $this->guessDeletionUpdates($entity);
        }

        $pendings = [];
        if ($entity instanceof ProductValueInterface) {
            if ($product = $entity->getEntity()) {
                $pendings[] = $product;
            }
        } elseif ($entity instanceof ProductMediaInterface) {
            $pendings[] = $entity->getValue()->getEntity();
        } elseif ($entity instanceof ProductPriceInterface || $entity instanceof MetricInterface) {
            $changeset = $this->filterChangeset($em->getUnitOfWork()->getEntityChangeSet($entity));
            if (!empty($changeset)) {
                $pendings[] = $entity->getValue()->getEntity();
            }
        }

        return $pendings;
    }

    /**
     * Guess product updates related to the deletion of a product price, media or metric
     *
     * @param object $entity
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface[]
     */
    protected function guessDeletionUpdates($entity)
    {
        $pendings = [];
        if ($entity instanceof ProductPriceInterface
            || $entity instanceof ProductMediaInterface
            || $entity instanceof MetricInterface) {
            $pendings[] = $entity->getValue()->getEntity();
        }

        return $pendings;
    }

    /**
     * Filter entity changeset to remove values that are the same
     *
     * @param array $changeset
     *
     * @return array
     */
    protected function filterChangeset(array $changeset)
    {
        return array_filter(
            $changeset,
            function ($item) {
                return $item[0] != $item[1];
            }
        );
    }
}
