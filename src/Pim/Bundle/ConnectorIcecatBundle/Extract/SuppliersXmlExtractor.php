<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Extract;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

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
class SuppliersXmlExtractor implements ExtractInterface, ReadInterface
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
     * Xml read content with the http reader
     * @var string
     */
    protected $xmlContent;

    /**
     * Download the archive to the given url then extract it in file path
     * @param string $url
     * @param string $login
     * @param string $password
     */
    public function __construct($url, $login, $password)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ExtractInterface::extract()
     */
    public function extract()
    {
        $this->read($this->url);
    }
    
    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::read()
     */
    public function read($url)
    {
        $fileReader = new FileHttpReader();
        $this->xmlContent = $fileReader->process($url, $this->login, $this->password);
    }
    
    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Extract.ReadInterface::getReadContent()
     */
    public function getReadContent()
    {
        return $this->xmlContent;
    }
}