<?php
namespace Pim\Bundle\ProductBundle\Manager;

use Oro\Bundle\DataAuditBundle\Entity\Audit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\ProductBundle\Entity\Product;

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
     * @param Product $product
     *
     * @return ArrayCollection
     */
    public function getLogEntries(Product $product)
    {
        $repo = $this->objectManager->getRepository('Oro\Bundle\DataAuditBundle\Entity\Audit');
        $logs = $repo->getLogEntries($product);

        return $logs;
    }

    /**
     * Return first log entry
     *
     * @param Product $product
     *
     * @return Audit
     */
    public function getFirstLogEntry(Product $product)
    {
        $logs = $this->getLogEntries($product);

        return (!empty($logs)) ? current($logs) : null;
    }

    /**
     * Return last log entry
     *
     * @param Product $product
     *
     * @return Audit
     */
    public function getLastLogEntry(Product $product)
    {
        $logs = $this->getLogEntries($product);

        return (!empty($logs)) ? end($logs) : null;
    }
}
