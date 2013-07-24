<?php

namespace Pim\Bundle\ImportExportBundle\Configuration;

use Pim\Bundle\BatchBundle\Configuration\AbstractConfiguration;

use JMS\Serializer\Annotation\Type;

/**
 * Export attribute configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportAttributeConfiguration extends AbstractConfiguration
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
    protected $locales;

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
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportAttributeConfiguration
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get locales
     *
     * @return string
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Set locales
     *
     * @param string $locales
     *
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportAttributeConfiguration
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTypeServiceId()
    {
        return 'pim_configuration_export_attribute';
    }
}
