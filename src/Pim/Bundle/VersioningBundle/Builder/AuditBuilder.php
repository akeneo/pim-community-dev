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
        $oldData = $previous ? $previous->getData() : [];

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
    protected function buildDiffData(array $oldData, array $newData)
    {
        $diffData = $this->filterDiffData($this->getMergedData($oldData, $newData));

        if (!empty($diffData) && $this->context) {
            $diffData['context'] = ['old' => '', 'new' => $this->context];
        }

        return $diffData;
    }

    /**
     * Merge the old and new data
     *
     * @param array $oldData
     * @param array $newData
     *
     * @return array
     */
    protected function getMergedData(array $oldData, array $newData)
    {
        $newData = array_map(
            function ($newItem) {
                return ['new' => $newItem];
            },
            $newData
        );

        $oldData = array_map(
            function ($oldItem) {
                return ['old' => $oldItem];
            },
            $oldData
        );

        $mergedData = array_merge_recursive($newData, $oldData);

        return array_map(
            function ($mergedItem) {
                return [
                    'old' => array_key_exists('old', $mergedItem) ? $mergedItem['old'] : '',
                    'new' => array_key_exists('new', $mergedItem) ? $mergedItem['new'] : ''
                ];
            },
            $mergedData
        );
    }

    /**
     * Filter diff data to remove values that are the same
     *
     * @param array $diffData
     *
     * @return array
     */
    protected function filterDiffData(array $diffData)
    {
        return array_filter(
            $diffData,
            function ($diffItem) {
                return $diffItem['old'] != $diffItem['new'];
            }
        );
    }
}
