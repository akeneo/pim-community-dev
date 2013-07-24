<?php

namespace Pim\Bundle\ImportExportBundle\Configuration;

use Pim\Bundle\BatchBundle\Configuration\AbstractConfiguration;

use JMS\Serializer\Annotation\Type;

/**
 * Export category configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExportCategoryConfiguration extends AbstractConfiguration
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
     * @var string
     *
     * @Type("string")
     */
    protected $tree;

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
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportCategoryConfiguration
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
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportCategoryConfiguration
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * Get tree
     *
     * @return string
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * Set tree
     *
     * @param string $tree
     *
     * @return \Pim\Bundle\ImportExportBundle\Configuration\ExportCategoryConfiguration
     */
    public function setTree($tree)
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTypeServiceId()
    {
        return 'pim_configuration_export_category';
    }
}
