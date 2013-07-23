<?php

namespace Pim\Bundle\ImportExportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pim\Bundle\ConfigBundle\Entity\Channel;
use Pim\Bundle\ImportExportBundle\Model\ExportInterface;

/**
 * Export product have single table inheritance with Export entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity()
 */
class ExportProduct extends Export implements ExportInterface
{
    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\ConfigBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     */
    protected $channel;

    /**
     * Get channel
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel
     *
     * @param Channel $channel
     *
     * @return \Pim\Bundle\ImportExportBundle\Entity\ExportProduct
     */
    public function setChannel(Channel $channel = null)
    {
        $this->channel = $channel;

        return $this;
    }
}
