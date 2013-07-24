<?php

namespace Pim\Bundle\ImportExportBundle\Configuration;

use Pim\Bundle\BatchBundle\Configuration\AbstractConfiguration;

use JMS\Serializer\Annotation\Type;

/**
 * Export product configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportProductConfiguration extends AbstractConfiguration
{
    /**
     * @var integer
     *
     * @Type("integer")
     */
    protected $content;

    /**
     * @var string
     *
     * @Type("string")
     */
    protected $channel;

    /**
     * Get content
     *
     * @return integer
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param integer $content
     *
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportProductConfiguration
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel
     *
     * @param string $channel
     *
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportProductConfiguration
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTypeServiceId()
    {
        return 'pim_configuration_export_product';
    }
}
