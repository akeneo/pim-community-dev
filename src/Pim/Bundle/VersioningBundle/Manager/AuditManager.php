<?php

namespace Pim\Bundle\VersioningBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataAuditBundle\Entity\Audit;

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
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Return product logs
     *
     * @param object $versionable
     *
     * @return ArrayCollection
     */
    public function getLogEntries($versionable)
    {
        $repo = $this->em->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit');
        $logs = $repo->getLogEntries($versionable);

        return $logs;
    }

    /**
     * Return the oldest log entry. A the log is order by date
     * desc, it means the very last line of the log
     *
     * @param object $versionable
     *
     * @return Audit
     */
    public function getOldestLogEntry($versionable)
    {
        $logs = $this->getLogEntries($versionable);

        return (!empty($logs)) ? end($logs) : null;
    }

    /**
     * Return the newest log entry. As the log is order by date
     * desc, it means the first line of the log
     *
     * @param object $versionable
     *
     * @return Audit
     */
    public function getNewestLogEntry($versionable)
    {
        $logs = $this->getLogEntries($versionable);

        return (!empty($logs)) ? reset($logs) : null;
    }
}
