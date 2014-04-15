<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser\MongoDBODM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ContainsProductsUpdateGuesser as BaseContainsProductsUpdateGuesser;

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
        $this->registry     = $registry;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = array();

        if ($entity instanceof Group && $entity->getId()) {
            $products = $this->registry->getRepository($this->productClass)->findAllForGroup($entity);
            foreach ($products as $product) {
                $pendings[] = $product;
            }

        } elseif ($entity instanceof CategoryInterface && $entity->getId()) {
            $products = $this->registry->getRepository($this->productClass)->findAllForCategory($entity);
            foreach ($products as $product) {
                $pendings[] = $product;
            }
        }

        return $pendings;
    }
}
