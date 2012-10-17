<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;

use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

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
class LanguagesExtract extends IcecatExtract implements DownloadInterface, UnpackInterface
{
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::initialize()
     */
    public function initialize()
    {
        $this->forced = false;
    }

    public function extract($url, $file)
    {
        $file = '/tmp/'.$file;
        $archivedFile = $file .'.gz';

        $this->download($url, $file);
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

    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::process()
     */
    /*public function process($url, $file)
    {
        $this->url = $url;
        $this->file = $file;
        $this->baseDir = '/tmp/'; // TODO : may be in config definition

        $this->prepareUrl();
        $this->download(LanguagesService::URL, LanguagesService::XML_FILE_ARCHIVE);
        $this->unzip(LanguagesService::XML_FILE_ARCHIVE, LanguagesService::XML_FILE);
    }*/

    /*public function prepareUrl()
    {
        $this->archiveFilePath = $this->baseDir . $this->file .'.gz';
    }*/
}