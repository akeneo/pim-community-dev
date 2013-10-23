<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
use Pim\Bundle\VersioningBundle\Entity\Version;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\TranslationBundle\Entity\AbstractTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Version builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionBuilder
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer   = $serializer;
    }

    /**
     * Build a version from a versionable entity
     *
     * @param VersionableInterface $versionable
     * @param User                 $user
     *
     * @return \Pim\Bundle\VersioningBundle\Entity\Version
     */
    public function buildVersion(VersionableInterface $versionable, User $user)
    {
        $resourceName = get_class($versionable);
        $resourceId   = $versionable->getId();
        $numVersion   = $versionable->getVersion();
        // TODO: we don't use direct json serialize due to convert to audit data based on array_diff
        $data         = $this->serializer->normalize($versionable, 'csv');

        return new Version($resourceName, $resourceId, $numVersion, $data, $user);
    }

    /**
     * Check if some entities must be versioned due to an entity changes
     *
     * @param EntityManager $em
     * @param object        $entity
     *
     * @return array
     */
    public function checkScheduledUpdate($em, $entity)
    {
        $pendings = array();

        if ($entity instanceof ProductAttribute) {
            $pendings[]= $entity;
            $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity);
            if ($changeset and in_array('group', array_keys($changeset))) {
                $groupChangeset = $changeset['group'];
                if (isset($groupChangeset[0]) and $groupChangeset[0]) {
                    $pendings[]= $groupChangeset[0];
                }
                if (isset($groupChangeset[1]) and $groupChangeset[1]) {
                    $pendings[]= $groupChangeset[1];
                }
            }
        } elseif ($entity instanceof Group) {
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

        } elseif ($entity instanceof VersionableInterface) {
            $pendings[]= $entity;

        } elseif ($entity instanceof ProductValueInterface) {
            $product = $entity->getEntity();
            if ($product) {
                $pendings[]= $product;
            }

        } elseif ($entity instanceof ProductPrice) {
            $pendings[]= $entity->getValue()->getEntity();

        } elseif ($entity instanceof AbstractTranslation) {
            $translatedEntity = $entity->getForeignKey();
            if ($translatedEntity instanceof VersionableInterface) {
                $pendings[]= $translatedEntity;
            }

        } elseif ($entity instanceof AttributeOption) {
            $pendings[]= $entity->getAttribute();

        } elseif ($entity instanceof AttributeOptionValue) {
            $pendings[]= $entity->getOption()->getAttribute();
        }

        return $pendings;
    }

    /**
     * Check if a related entity must be versioned due to entity deletion
     *
     * @param object $entity
     *
     * @return array
     */
    public function checkScheduledDeletion($entity)
    {
        $pendings = array();

        if ($entity instanceof AttributeOption) {
            $pendings[]= $entity->getAttribute();

        } elseif ($entity instanceof Group or $entity instanceof Category) {
            $products = $entity->getProducts();
            foreach ($products as $product) {
                $pendings[]= $product;
            }
        }

        return $pendings;
    }

}
