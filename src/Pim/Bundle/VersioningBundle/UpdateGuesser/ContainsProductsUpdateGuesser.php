<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Model\Group;
use Pim\Bundle\CatalogBundle\Model\Category;

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
    public function supportAction($action)
    {
        return in_array(
            $action,
            array(UpdateGuesserInterface::ACTION_UPDATE_ENTITY, UpdateGuesserInterface::ACTION_DELETE)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(Entitymanager $em, $entity, $action)
    {
        $pendings = array();

        if ($entity instanceof Group) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[] = $product;
            }

        } elseif ($entity instanceof Category) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        }

        return $pendings;
    }
}
