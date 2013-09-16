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

    protected $exportActions = array();

    public function addExportAction(ExportActionInterface $action)
    {
        $this->exportActions[] = $action;
    }

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
     * Serialize datagrid results in a specific format and with a specific context
     * @param string $format
     * @param array  $context
     *
     * @return string
     */
    public function exportData($format, array $context = array())
    {
        return $this->serializer->serialize(
            $this->getResultsWithoutPaging(),
            $format,
            $context
        );
    }

    /**
     * Get query result without pagination
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getResultsWithoutPaging()
    {
        $this->pagerApplied = true;
        $this->applyParameters();

        // allow to get all the columns
        $this->query->select($this->query->getRootAlias());

        return $this->getQuery()->execute();
    }

    /**
     *Get all query result ids
     *
     * @return integer[]
     */
    public function getAllIds()
    {
        $this->pagerApplied = true;
        $this->sorterApplied = true;
        $this->applyParameters();
        $this->query->select($this->query->getRootAlias());
        $entities = $this->query->execute(array(), AbstractQuery::HYDRATE_ARRAY);
        $func = function($entity) {
            return $entity['id'];
        };
        $ids = array_unique(array_map($func, $entities));

        return $ids;
    }

}
