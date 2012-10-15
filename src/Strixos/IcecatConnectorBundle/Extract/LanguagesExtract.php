<?php
namespace Strixos\IcecatConnectorBundle\Extract;

use Strixos\IcecatConnectorBundle\Extract\IcecatExtract;

use Strixos\IcecatConnectorBundle\Model\Service\LanguagesService;

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
class LanguagesExtract extends IcecatExtract
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
        // TODO : Change constants.. Call configuration files ?
        $this->download(LanguagesService::URL, LanguagesService::XML_FILE_ARCHIVE);
        $this->unzip(LanguagesService::XML_FILE_ARCHIVE, LanguagesService::XML_FILE);
    }
}