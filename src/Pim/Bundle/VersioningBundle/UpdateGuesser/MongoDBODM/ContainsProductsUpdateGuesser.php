<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser\MongoDBODM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ContainsProductsUpdateGuesser as BaseContainsProductsUpdateGuesser;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\GroupInterface;

/**
 * Contains product update guesser for MongoDB
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContainsProductsUpdateGuesser extends BaseContainsProductsUpdateGuesser
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $productClass
     */
    public function __construct(ManagerRegistry $registry, $productClass)
    {
        $this->registry = $registry;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = [];

        if ($entity instanceof GroupInterface && $entity->getId()) {
            $products = $this->registry->getRepository($this->productClass)->findAllForGroup($entity);
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        } elseif ($entity instanceof CategoryInterface &&
                  $entity->getId() &&
                  UpdateGuesserInterface::ACTION_DELETE === $action
        ) {
            $products = $this->registry->getRepository($this->productClass)->findAllForCategory($entity);
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        }

        return $pendings;
    }
}
