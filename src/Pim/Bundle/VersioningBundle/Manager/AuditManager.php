<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\VersioningBundle\Entity\VersionableInterface;

/**
 * Audit manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuditManager
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * {@inheritDoc}
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Return product logs
     *
     * @param VersionableInterface $versionable
     *
     * @return ArrayCollection
     */
    public function getLogEntries(VersionableInterface $versionable)
    {
        $repo = $this->objectManager->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit');
        $logs = $repo->getLogEntries($versionable);

        return $logs;
    }

    /**
     * Return first log entry
     *
     * @param VersionableInterface $versionable
     *
     * @return Audit
     */
    public function getFirstLogEntry(VersionableInterface $versionable)
    {
        $logs = $this->getLogEntries($versionable);

        return (!empty($logs)) ? current($logs) : null;
    }

    /**
     * Return last log entry
     *
     * @param VersionableInterface $versionable
     *
     * @return Audit
     */
    public function getLastLogEntry(VersionableInterface $versionable)
    {
        $logs = $this->getLogEntries($versionable);

        return (!empty($logs)) ? end($logs) : null;
    }
}
