<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\IcecatConnectorBundle\Extract\IcecatExtract;

use Strixos\IcecatConnectorBundle\Model\Service\ProductService;

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
class ProductExtract extends IcecatExtract
{
    
    private $url;
    private $fileArchivePath;
    private $filePath;
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::initialize()
     */
    public function initialize()
    {
        $this->forced = false;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\Extract\AbstractExtract::process()
     */
    public function process($productId, $supplierName, $locale)
    {
        $this->prepareUrl($productId, $supplierName, $locale);
        $this->download($this->url, $this->fileArchivePath);
        $this->unzip($this->fileArchivePath, $this->filePath);
        
        return $this->filePath;
    }
    
    private function prepareUrl($productId, $supplierName, $locale)
    {
        $this->url = ProductService::BASE_URL .
                '?prod_id='.$productId.';vendor='.$supplierName.';lang='.$locale.';output=productxml';
        $this->filePath = '/tmp/product-'. $productId .'-'. $locale .'.xml';
        $this->fileArchivePath = $this->filePath .'.gz';
    }
    
    private function getFilePath()
    {
        return $this->filePath;
    }
}