<?php

namespace Pim\Bundle\GridBundle\Datagrid;

use Pim\Bundle\GridBundle\Action\Export\ExportActionInterface;
use Symfony\Component\Serializer\Serializer;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\GridBundle\Datagrid\Datagrid as OroDatagrid;

/**
 * Override of OroPlatform datagrid
 * DatagridBuilder set serializer to this class allowing quick export feature
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Datagrid extends OroDatagrid
{
    /**
     * @var Serializer
     */
    protected $serializer;

    static private $applied = false;

    /**
     * @var ExportActionInterface[]
     */
    protected $exportActions = array();

    /**
     * Add an export action
     * @param ExportActionInterface $action
     */
    public function addExportAction(ExportActionInterface $action)
    {
        $this->exportActions[] = $action;
    }

    /**
     * Get list of export actions
     *
     * @return ExportActionInterface[]
     */
    public function getExportActions()
    {
        return $this->exportActions;
    }

    /**
     * Setter serializer
     *
     * @param Serializer $serializer
     *
     * @return \Pim\Bundle\GridBundle\Datagrid\Datagrid
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Returns the results count of the query
     *
     * @return integer
     */
    public function countResults()
    {
        $this->pagerApplied = true;
        $this->applyParameters();

        $exprFieldId = sprintf('%s.id', $this->query->getRootAlias());
        $this->query->select($exprFieldId);
        $this->query->groupBy($exprFieldId);

        return count($this->query->execute());
    }

    /**
     * Serialize datagrid results in a specific format and with a specific context
     * @param string $format
     * @param array  $context
     *
     * @return string
     */
    public function exportData($format, $offset = 0, $limit = 250, array $context = array())
    {
        $results = $this->getBatchedResults($offset, $limit);

        $data = $this->serializer->serialize($results, $format, $context);

        $em = $this->query->getEntityManager();
        $em->clear();

        return $data;
    }

    /**
     * Get query result without pagination
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBatchedResults($offset, $limit)
    {
        $this->pagerApplied = true;
        $this->applyParameters();
        if (!static::$applied) {
            $this->query->resetDQLPart('groupBy');
//             $this->query->resetDQLPart('orderBy');

            $this->query->select($this->query->getRootAlias());
            $this->query->addSelect('values');
            $this->query->addSelect('family');
            $this->query->addSelect('valuePrices');

            $this->query->leftJoin('values.options', 'valueOptions');
            $this->query->addSelect('valueOptions');

            $this->query->leftJoin('o.categories', 'categories');
            $this->query->addSelect('categories');

//             $this->query->leftJoin('values.attribute', 'attribute');
//             $attributesList = $this->getAttributeAvailableIds();
//             $exprIn = $this->query->expr()->in('attribute', $attributesList);
//             $this->query->andWhere($exprIn);

//             var_dump($this->query->getDQLPart('where')); die;
//             var_dump($this->query->getDQLPart('join'));die;
//             $this->query->leftJoin('values.attribute', 'attribute');
//             $this->query->addOrderBy('attribute.id');

            static::$applied = true;
        }
        $this->query->setFirstResult($offset);
        $this->query->setMaxResults($limit);

        return $this->query->execute();
    }

    public function getAttributeAvailableIds()
    {
        $qb = clone $this->query;

        $qb->leftJoin('values.attribute', 'attribute');
        $qb->groupBy('attribute.code');
        $qb->select('attribute.code, attribute.translatable, attribute.attributeType');

        $attributesList = array();
        $results = $qb->getQuery()->execute();
        foreach ($results as $attribute) {
            if ($attribute['translatable'] == 1) {
                $attributesList[] = $attribute['code'].'-fr_FR';
                $attributesList[] = $attribute['code'].'-en_US';
            } elseif ($attribute['attributeType'] === 'pim_catalog_identifier') {
                array_unshift($attributesList, $attribute['code']);
            } else {
                $attributesList[] = $attribute['code'];
            }

        }

        return $attributesList;
    }
}
