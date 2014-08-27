<?php

namespace Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Attribute option update guesser
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionUpdateGuesser implements UpdateGuesserInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $productClass;

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
    public function guessUpdates(EntityManager $em, $entity, $action)
    {
        $pendings = array();
        if ($entity instanceof AttributeOption) {
            $pendings[] = $entity->getAttribute();

            if ($action === UpdateGuesserInterface::ACTION_DELETE) {
                $products = $this
                    ->registry
                    ->getRepository($this->productClass)
                    ->findAllWithAttributeOption($entity);

                foreach ($products as $product) {
                    $pendings[] = $product;
                }
            }
        } elseif ($entity instanceof AttributeOptionValue) {
            $pendings[] = $entity->getOption()->getAttribute();
        }

        return $pendings;
    }
}
