<?php
namespace Strixos\IcecatConnectorBundle\Model\Extract;

use Strixos\IcecatConnectorBundle\Model\Service\SuppliersService;

/**
 * 
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SuppliersExtract extends IcecatExtract
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
        $this->download(SuppliersService::URL, SuppliersService::XML_FILE_ARCHIVE);
        $this->unzip(SuppliersService::XML_FILE_ARCHIVE, SuppliersService::XML_FILE);
    }
}