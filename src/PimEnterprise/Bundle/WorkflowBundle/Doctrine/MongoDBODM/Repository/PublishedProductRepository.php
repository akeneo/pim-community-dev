<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM\Repository;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface,
 PublishedAssociationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProduct(ProductInterface $originalProduct)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('originalProduct.$id')->equals(new \MongoId($originalProduct->getId()));
        $result = $qb->getQuery()->execute();
        $product = $result->getNext();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalProductId)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('originalProduct.$id')->equals(new \MongoId($originalProductId));
        $result = $qb->getQuery()->execute();
        $product = $result->getNext();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByVersionId($versionId)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('version.$id')->equals(new \MongoId($versionId));
        $result = $qb->getQuery()->execute();
        $product = $result->getNext();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProducts(array $originalProducts)
    {
        $originalIds = [];
        foreach ($originalProducts as $product) {
            $originalIds[] = new \MongoId($product->getId());
        }

        $qb = $this->createQueryBuilder();
        $qb->field('originalProduct.$id')->in($originalIds);
        $products = $qb->getQuery()->execute();

        return $products->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedVersionIdByOriginalProductId($originalId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('version');
        $qb->field('originalProduct.$id')->equals(new \MongoId($originalId));
        $qb->hydrate(false);

        $results = $qb->getQuery()->execute();

        foreach ($results as $result) {
            return $result['version']['$id']->{'$id'};
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping(array $originalIds = [])
    {
        $qb = $this->createQueryBuilder();
        $qb->select('originalProduct', '_id');
        if (!empty($originalIds)) {
            foreach ($originalIds as $key => $originalId) {
                $originalIds[$key] = new \MongoId($originalId);
            }
            $qb->field('originalProduct.$id')->in($originalIds);
        }

        $qb->hydrate(false);

        $ids = [];
        foreach ($qb->getQuery()->execute() as $row) {
            $originalProductId = $row['originalProduct']['$id']->{'$id'};
            $ids[$originalProductId] = $row['_id']->{'$id'};
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     *
     * TODO; find a way to do it efficiently
     */
    public function findOneByTypeAndOwner(AssociationTypeInterface $type, $ownerId)
    {
        // retrieve the product that owns our published association
        $product = $this->find($ownerId);

        // find the right association
        foreach ($product->getAssociations() as $association) {
            if ($association->getAssociationType() === $type) {
                return $association;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: maybe use normalisation for associations and remove the $nbAssociationTypes parameter
     */
    public function removePublishedProduct(PublishedProductInterface $published, $nbAssociationTypes = null)
    {
        if (null === $nbAssociationTypes) {
            throw new \LogicException('The parameter "$nbAssociationTypes" can not be null');
        }

        $mongoRef = [
            '$ref' => $this->dm->getClassMetadata(get_class($published))->getCollection(),
            '$id'  => new \MongoId($published->getId()),
            '$db'  => $this->dm->getConfiguration()->getDefaultDB(),
        ];

        $collection = $this->dm->getDocumentCollection(get_class($published));

        // the query to perform here is
        /*
         db.pimee_workflow_published_product.update(
             { associations: {
                 $elemMatch : {
                     products: { $ref:'pimee_workflow_published_product', $id:ObjectId('ID'), $db: 'DB'}
                 }
             }},
             { $pull: {
                 'associations.$.products': { $ref:'pimee_workflow_published_product', $id:ObjectId('ID'), $db: 'DB'}}
             },
             { 'multiple': 1 }
         );
        */

        // we iterate over the number of association types because the query removes only the product that
        // belongs to the first association (instead of removing it in existing associations)
        for ($i = 0; $i < $nbAssociationTypes; $i++) {
            $collection->update(
                [
                    'associations' => [
                        '$elemMatch' => [
                            'products' => $mongoRef
                        ]
                    ]
                ],
                [
                    '$pull' => [
                        'associations.$.products' => $mongoRef
                    ]
                ],
                [ 'multiple' => 1 ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForFamily(FamilyInterface $family)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->field('family')->equals($family->getId());

        return $qb->getQuery()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForCategoryAndChildren($categoryIds)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->field('categoryIds')->in($categoryIds);

        return $qb->getQuery()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttribute(AttributeInterface $attribute)
    {
        return $this->createQueryBuilder('pp')
            ->field('values.attribute')->equals($attribute->getId())
            ->getQuery()
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForGroup(GroupInterface $group)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->field('groupIds')->in([$group->getId()]);

        return $qb->getQuery()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAssociationType(AssociationTypeInterface $associationType)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->field('associations.associationType')->equals($associationType->getId());

        return $qb->getQuery()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function countPublishedProductsForAttributeOption(AttributeOptionInterface $option)
    {
        $qb = $this->createQueryBuilder('pp');

        if ($option->getAttribute()->getAttributeType() === AttributeTypes::OPTION_SIMPLE_SELECT) {
            $qb->field("values.option")->equals($option->getId());
        } else {
            $qb->field("values.optionIds")->equals($option->getId());
        }

        return $qb->getQuery()->count();
    }
}
