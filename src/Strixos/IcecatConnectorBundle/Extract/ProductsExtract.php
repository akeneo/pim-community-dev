<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\IcecatConnectorBundle\Extract\IcecatExtract;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : URL must be set in configuration files
 *
 */
class ProductsExtract extends IcecatExtract
{
    const URL          = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
    const FILE_ARCHIVE = '/tmp/export_urls_rich.txt.gz';
    const FILE         = '/tmp/export_urls_rich.txt';

    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::initialize()
     */
    public function initialize()
    {
        $this->forced = false;
    }

    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::process()
     */
    public function process()
    {
        $this->download(self::URL, self::FILE_ARCHIVE);
        $this->unzip(self::FILE_ARCHIVE, self::FILE);
    }
}