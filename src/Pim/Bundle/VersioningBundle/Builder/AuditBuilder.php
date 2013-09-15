<?php

namespace Pim\Bundle\VersioningBundle\Builder;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;
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

        $diffData = array();
        foreach ($merge as $changedField => $data) {
            if ($data['old'] != $data['new']) {
                $diffData[$changedField]= $data;
            }
        }

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
}
