<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Read;

use Pim\Bundle\DataFlowBundle\Model\Extract\FileHttpReader;

/**
 * Read supplier xml content
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SuppliersXmlUrl implements ExtractInterface
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
     *
     * @param string $url      file url
     * @param string $login    login
     * @param string $password password
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
        $fileReader = new FileHttpReader();
        $this->xmlContent = $fileReader->process($this->url, $this->login, $this->password);
    }

    /**
     * Get xml content
     * @return string
     */
    public function getXmlContent()
    {
        return $this->xmlContent;
    }
}
