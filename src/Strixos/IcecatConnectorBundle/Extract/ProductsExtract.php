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
class ProductsExtract implements ExtractInterface
{
    const URL          = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
    const FILE_ARCHIVE = '/tmp/export_urls_rich.txt.gz';
    const FILE         = '/tmp/export_urls_rich.txt';

    const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';

    /**
     * Constructor
     *
     * @param string $forceDownloadFile : if false use file system file if exists
     */
    public function __construct($forceDownloadFile = false)
    {
        $this->forced = $forceDownloadFile;
    }

    public function extract()
    {
        $this->download(self::URL, self::FILE_ARCHIVE);
        $this->unpack(self::FILE_ARCHIVE, self::FILE);
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