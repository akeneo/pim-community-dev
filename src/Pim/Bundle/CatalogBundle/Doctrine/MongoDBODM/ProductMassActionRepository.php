<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

/**
 * Mass action repository for product documents
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassActionRepository implements ProductMassActionRepositoryInterface
{
    /** @var string */
    protected $documentName;

    /** @var DocumentManager */
    protected $dm;

    /** @var FamilyRepository */
    protected $familyRepository;

    /**
     * @param DocumentManager  $dm
     * @param string           $documentName
     * @param FamilyRepository $familyRepository
     */
    public function __construct(DocumentManager $dm, $documentName, FamilyRepository $familyRepository)
    {
        $this->dm = $dm;
        $this->documentName     = $documentName;
        $this->familyRepository = $familyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, $values)
    {
        // manage inset for selected entities
        if ($values) {
            $qb->field('_id');
            $inset ? $qb->in($values) : $qb->notIn($values);
        }

        // remove limit of the query
        $qb->limit(null);
    }

    /**
     * {@inheritdoc}
     */
    public function findCommonAttributeIds(array $productIds)
    {
        $results = $this->findValuesCommonAttributeIds($productIds);

        $familyIds = $this->findFamiliesFromProductIds($productIds);
        if (!empty($familyIds)) {
            $families = $this->familyRepository->findAttributeIdsFromFamilies($familyIds);
        }

        $attIds = null;
        foreach ($results as $result) {
            $familyAttr = isset($result['_id']['family']) ? $families[$result['_id']['family']] : array();
            $prodAttIds = array_unique(array_merge($result['attribute'], $familyAttr));
            if (null === $attIds) {
                $attIds = $prodAttIds;
            } else {
                $attIds = array_intersect($attIds, $prodAttIds);
            }
        }

        return $attIds;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            throw new \LogicException('No products to remove');
        }

        $qb = $this->dm->createQueryBuilder($this->documentName);
        $qb
            ->remove()
            ->field('_id')->in($ids);

        $result = $qb->getQuery()->execute();

        return $result['n'];
    }

    /**
     * Find all common attribute ids with values from a list of product ids
     * Only exists for ODM repository
     *
     * @param array $productIds
     *
     * @return array
     */
    protected function findValuesCommonAttributeIds(array $productIds)
    {
        $collection = $this->dm->getDocumentCollection($this->documentName);

        $expr = new Expr($this->dm);
        $class = $this->getClassMetadata(get_class($this->documentName));
        $expr->setClassMetadata($class);
        $expr->field('_id')->in($productIds);

        $pipeline = array(
            array('$match' => $expr->getQuery()),
            array('$unwind' => '$values'),
            array(
                '$group'  => array(
                    '_id'       => array('id' => '$_id', 'family' => '$family'),
                    'attribute' => array( '$addToSet' => '$values.attribute')
                )
            )
        );

        return $collection->aggregate($pipeline)->toArray();
    }

    /**
     * Find family from list of products
     *
     * @param array $productIds
     *
     * @return array
     */
    protected function findFamiliesFromProductIds(array $productIds)
    {
        $qb = $this->dm->createQueryBuilder($this->documentName);
        $qb
            ->field('_id')->in($productIds)
            ->distinct('family')
            ->hydrate(false);

        $cursor = $qb->getQuery()->execute();

        return $cursor->toArray();
    }
}
