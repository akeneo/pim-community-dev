<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Read;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\ExtractInterface;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\DownloadInterface;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\UnpackInterface;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileUnzip;
use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpDownload;


/**
 * Download a source archive (product, supplier, language base data) and unpack to a destination file
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DownloadAndUnpackFromUrl implements ExtractInterface, DownloadInterface, UnpackInterface
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
     * Download the archive to the given url then extract it in file path
     *
     * @param string  $url         archive url
     * @param string  $login       login
     * @param string  $password    password
     * @param string  $archivePath download path
     * @param string  $filePath    extract path
     * @param boolean $force       force to download
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
     * {@inheritDoc}
     */
    public function extract()
    {
        $this->download($this->url, $this->archivePath);
        $this->unpack($this->archivePath, $this->filePath);
    }

    /**
     * {@inheritDoc}
     */
    public function download($url, $file)
    {
        $downloader = new FileHttpDownload();
        $downloader->process($url, $file, $this->login, $this->password, $this->force);
    }

    /**
     * {@inheritDoc}
     */
    public function unpack($archivedFile, $file)
    {
        $unpacker = new FileUnzip();
        $unpacker->process($archivedFile, $file, $this->force);
    }

}
