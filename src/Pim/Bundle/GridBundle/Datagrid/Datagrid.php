<?php

namespace Pim\Bundle\GridBundle\Datagrid;

use Pim\Bundle\GridBundle\Action\Export\ExportActionInterface;
use Symfony\Component\Serializer\Serializer;
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
        $this->applyParameters();
        $proxyQuery = clone $this->query;

        $exprFieldId = sprintf('%s.id', $proxyQuery->getRootAlias());
        $proxyQuery
            ->select($exprFieldId)
            ->groupBy($exprFieldId)
            ->resetDQLPart('orderBy')
            ->setFirstResult(null)
            ->setMaxResults(null);

        return count($proxyQuery->execute());
    }

    /**
     * Returns the query with parameters applied
     *
     * @return \Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface
     */
    public function getQueryWithParametersApplied()
    {
        $this->applyParameters();

        return $this->query;
    }

    /**
     * Serialize datagrid results in a specific format and with a specific context
     * Offset and limit allow to batch the export result and streamed the response if needed
     *
     * @param ProxyQueryInterface $proxyQuery
     * @param string              $format
     * @param array               $context
     * @param int                 $offset
     * @param int                 $limit
     *
     * @return string
     */
    public function exportData($proxyQuery, $format, array $context = array(), $offset = null, $limit = null)
    {
        $proxyQuery
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $results = $proxyQuery->execute();
        $data = $this->serializer->serialize($results, $format, $context);

        $proxyQuery->getEntityManager()->clear();

        return $data;
    }
}
