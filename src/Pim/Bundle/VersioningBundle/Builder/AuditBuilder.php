<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Audit builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditBuilder
{
    /**
     * Context data to add to audit
     *
     * @var string
     */
    protected $context;

    /**
     * Set context
     *
     * @param string $context
     *
     * @return AuditBuilder
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Create a log entry from current and previous version
     *
     * @param Version $current
     * @param Version $previous
     * @param integer $versionNumber
     *
     * @return Audit
     */
    public function buildAudit(Version $current, Version $previous = null, $versionNumber = 1)
    {
        $newData = $current->getData();
        if ($previous) {
            $oldData = $previous->getData();
        } else {
            $oldData = array();
        }

        $diffData = $this->buildDiffData($oldData, $newData);

        $action = ($versionNumber > 1) ? 'update' : 'create';
        $audit = new Audit();
        $audit->setAction($action);
        $audit->setObjectClass($current->getResourceName());
        $audit->setLoggedAt();
        $audit->setObjectName($current->getResourceName());
        $audit->setObjectId($current->getResourceId());
        $audit->setVersion($versionNumber);
        $audit->setData($diffData);
        $audit->setUser($current->getUser());

        return $audit;
    }

    /**
     * Build diff data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    protected function buildDiffData($oldData, $newData)
    {
        $merge = $this->getMergedData($oldData, $newData);
        $diffData = array();
        foreach ($merge as $changedField => $data) {
            if ($data['old'] != $data['new'] || !isset($oldData[$changedField])) {
                $diffData[$changedField]= $data;
            }
        }

        if (!empty($diffData) && $this->context) {
            $diffData['context']= array('old' => '', 'new' => $this->context);
        }

        return $diffData;
    }

    /**
     * Merge data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    protected function getMergedData($oldData, $newData)
    {
        $merge = array();
        foreach ($newData as $field => $value) {
            $merge[$field]= array('old' => '', 'new' => $value);
        }
        foreach ($oldData as $field => $value) {
            if (!isset($merge[$field])) {
                $merge[$field]= array('old' => $value, 'new' => '');
            } else {
                $merge[$field]['old'] = $value;
            }
        }

        return $merge;
    }
}
