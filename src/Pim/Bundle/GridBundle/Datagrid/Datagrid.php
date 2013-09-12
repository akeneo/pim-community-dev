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

        // reset select and allow to get all the columns
        $this->query->select($this->query->getRootAlias());

        return $this->query->execute();
    }
}
