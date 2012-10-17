<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

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
class SuppliersExtract implements ExtractInterface, DownloadInterface, UnpackInterface
{
    const URL              = 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml';
    const XML_FILE_ARCHIVE = '/tmp/suppliers-list.xml.gz';
    const XML_FILE         = '/tmp/suppliers-list.xml';

    const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->forced = false;
    }

    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::process()
     */
    public function extract()
    {
        $this->download(self::URL, self::XML_FILE_ARCHIVE);
        $this->unpack(self::XML_FILE_ARCHIVE, self::XML_FILE);
    }

    /**
    * (non-PHPdoc)
    * @see \Strixos\IcecatConnectorBundle\Extract\DownloadInterface::download()
    */
    public function download($url, $file)
    {
        $downloader = new FileHttpDownload();
        $downloader->process($url, $file, self::AUTH_LOGIN, self::AUTH_PASSWORD, $this->forced);
    }

    /**
     *
     * @param unknown_type $archivedFile
     * @param unknown_type $file
     */
    public function unpack($archivedFile, $file)
    {
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFile, $file, $this->forced);
    }
}