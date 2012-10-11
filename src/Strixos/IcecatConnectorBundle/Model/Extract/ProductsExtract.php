<?php
namespace Strixos\IcecatConnectorBundle\Model\Extract;

use Strixos\IcecatConnectorBundle\Model\Service\ProductsService;

/**
 * 
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductsExtract extends IcecatExtract
{
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
    public function process()
    {
        $this->download(ProductsService::URL, ProductsService::XML_FILE_ARCHIVE);
        $this->unzip(ProductsService::XML_FILE_ARCHIVE, ProductsService::XML_FILE);
    }
}