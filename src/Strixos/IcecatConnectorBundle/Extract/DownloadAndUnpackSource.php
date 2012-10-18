<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

/**
 * Download a source archive (product, supplier, language base data) and unpack to a destination file
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DownloadAndUnpackSource implements ExtractInterface, DownloadInterface, UnpackInterface
{
    /**
     * Archive url
     * @var string
     */
    protected $url;

    /**
     * Login to connect on source
     * @var string
     */
    protected $login;

    /**
     * Password to connect on source
     * @var string
     */
    protected $password;

    /**
     * Archive path
     * @var string
     */
    protected $archivePath;

    /**
     * File path
     * @var string
     */
    protected $filePath;

    /**
     * Force archive download (else use already downloaded one)
     * @var boolean
     */
    protected $forceDownload;

    /**
     * Download the archive to the given url then extract it in file path
     * @param string $url
     * @param string $login
     * @param string $password
     * @param string $archivePath
     * @param string $filePath
     * @param boolean $forceDownloadFile
     */
    public function __construct($url, $login, $password, $archivePath, $filePath, $forceDownloadFile = false)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->archivePath = $archivePath;
        $this->filePath = $filePath;
        $this->forceDownload = $forceDownloadFile;
    }

    /**
     * (non-PHPdoc)
     * @see Strixos\IcecatConnectorBundle\Extract.ExtractInterface::extract()
     */
    public function extract()
    {
        $this->download($this->url, $this->archivePath);
        $this->unpack($this->archivePath, $this->filePath);
    }

    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Extract\DownloadInterface::download()
     */
    public function download($url, $file)
    {
        $downloader = new FileHttpDownload();
        $downloader->process($url, $file, $this->login, $this->password, $this->forceDownload);
    }

    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Extract\UnpackInterface::unpack()
     */
    public function unpack($archivedFile, $file)
    {
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFile, $file, $this->forceDownload);
    }
}