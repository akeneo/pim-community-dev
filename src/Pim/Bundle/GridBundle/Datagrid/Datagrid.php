<?php

namespace Pim\Bundle\GridBundle\Datagrid;

use Pim\Bundle\GridBundle\Action\Export\ExportActionInterface;
use Oro\Bundle\GridBundle\Datagrid\Datagrid as OroDatagrid;
use Symfony\Component\Serializer\Serializer;

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

    /**
     * @var ExportActionInterface[]
     */
    protected $exportActions = array();

    /**
     * Add export action
     *
     * @param ExportActionInterface $action
     */
    public function addExportAction(ExportActionInterface $action)
    {
        $this->exportActions[] = $action;
    }

    /**
     * Get list of export actions
     *
     * @return \Pim\Bundle\GridBundle\Datagrid\ExportActionInterface[]
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

        $this->query->select($this->query->getRootAlias());
        $this->query->setFirstResult($offset);
        $this->query->setMaxResults($limit);

        return $this->query->execute();
    }
}
