<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\DataAuditBundle\Entity\Audit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ProductBundle\Model\ProductInterface;

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
     * @param ProductInterface $product
     *
     * @return ArrayCollection
     */
    public function getLogEntries(ProductInterface $product)
    {
        $repo = $this->objectManager->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit');
        $logs = $repo->getLogEntries($product);

        return $logs;
    }

    /**
     * Return first log entry
     *
     * @param ProductInterface $product
     *
     * @return Audit
     */
    public function getFirstLogEntry(ProductInterface $product)
    {
        $logs = $this->getLogEntries($product);

        return (!empty($logs)) ? current($logs) : null;
    }

    /**
     * Return last log entry
     *
     * @param ProductInterface $product
     *
     * @return Audit
     */
    public function getLastLogEntry(ProductInterface $product)
    {
        $logs = $this->getLogEntries($product);

        return (!empty($logs)) ? end($logs) : null;
    }
}
