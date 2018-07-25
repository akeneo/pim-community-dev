<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Doctrine\ORM\EntityManager;

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
            [UpdateGuesserInterface::ACTION_UPDATE_ENTITY, UpdateGuesserInterface::ACTION_DELETE]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof GroupInterface) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        } elseif ($entity instanceof CategoryInterface && UpdateGuesserInterface::ACTION_DELETE === $action) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        }

        return $pendings;
    }
}
