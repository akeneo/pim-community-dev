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
class LanguagesExtract implements ExtractInterface, DownloadInterface, UnpackInterface
{
    const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';

    /**
     * Constructor
     *
     * @param string $url
     * @param string $file
     * @param string $forceDownloadFile : if false use file system file if exists
     */
    public function __construct($url, $file, $forceDownloadFile = false)
    {
        $this->url = $url;
        $this->file = $file;
        $this->forced = $forceDownloadFile;
    }

    public function extract()
    {
        $file = '/tmp/'.$this->file;
        $archivedFile = $file .'.gz';

        $this->download($this->url, $file);
        $this->unpack($archivedFile, $file);
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