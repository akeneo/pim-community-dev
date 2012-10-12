<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\DataFlowBundle\Model\Extract\AbstractExtract;

use Strixos\DataFlowBundle\Model\Extract\FileUnzip;
use Strixos\DataFlowBundle\Model\Extract\FileHttpDownload;

/**
 * aims to define a generic class for icecat connector extract flow
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @abstract
 *
 */
abstract class IcecatExtract extends AbstractExtract
{
    const AUTH_LOGIN    = 'NicolasDupont';
    const AUTH_PASSWORD = '1cec4t**)';
    
    /**
     * force or non download and extract archive
     * @var boolean
     */
    protected $forced;
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::download()
     */
    public function download($url, $path)
    {
        // get config for user login and password
        $downloader = new FileHttpDownload();
        $downloader->process($url, $path, self::AUTH_LOGIN, self::AUTH_PASSWORD, $this->forced);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::unzip()
     */
    public function unzip($archivePath, $filePath)
    {
        $unzipper = new FileUnzip();
        $unzipper->process($archivePath, $filePath, $this->forced);
    }
}