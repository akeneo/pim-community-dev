<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Extract;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;

/**
 * Download a source archive (product, supplier, language base data) and unpack to a destination file
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DownloadAndUnpackSource implements ExtractInterface, DownloadInterface, UnpackInterface, ReadInterface
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
     * Force archive download (else use already downloaded one) and unpack
     * @var boolean
     */
    protected $force;

    /**
     * Read content from file downloaded
     * @var string
     */
    protected $content;

    /**
     * Download the archive to the given url then extract it in file path
     * @param string  $url
     * @param string  $login
     * @param string  $password
     * @param string  $archivePath
     * @param string  $filePath
     * @param boolean $force
     */
    public function __construct($url, $login, $password, $archivePath, $filePath, $force = false)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
        $this->filePath = $filePath;
        $this->archivePath = $archivePath;
        $this->force = $force;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ExtractInterface::extract()
     */
    public function extract()
    {
        $this->download($this->url, $this->archivePath);
        $this->unpack($this->archivePath, $this->filePath);
    }

    /**
     * (non-PHPdoc)
     * @see \Pim\Bundle\ConnectorIcecatBundle\Extract\DownloadInterface::download()
     */
    public function download($url, $file)
    {
        $downloader = new FileHttpDownload();
        $downloader->process($url, $file, $this->login, $this->password, $this->force);
    }

    /**
     * (non-PHPdoc)
     * @see \Pim\Bundle\ConnectorIcecatBundle\Extract\UnpackInterface::unpack()
     */
    public function unpack($archivedFile, $file)
    {
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFile, $file, $this->force);
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::read()
     */
    public function read($file)
    {
        if (!$this->content) {
                $this->content = file_get_contents($file);
        }

        return $this->content;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::getReadContent()
     */
    public function getReadContent()
    {
        return $this->read($this->filePath);
    }
}
