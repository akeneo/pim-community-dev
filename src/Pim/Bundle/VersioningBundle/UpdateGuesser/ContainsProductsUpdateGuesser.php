<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Contains product update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContainsProductsUpdateGuesser implements UpdateGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guessUpdates(Entitymanager $em, $entity)
    {
        $pendings = array();

        if ($entity instanceof Group) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[]= $product;
            }

        } elseif ($entity instanceof Category) {
            $pendings[]= $entity;
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[]= $product;
            }
        }

        return $pendings;
    }
}
