<?php
namespace Strixos\IcecatConnectorBundle\Model\Service;

use Strixos\IcecatConnectorBundle\Model\Extract\LanguagesExtract;

use Strixos\DataFlowBundle\Model\Service\AbstractService;

/**
 * 
 * Service ETL to import languages list from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguagesService extends AbstractService
{
	/**
	 * @staticvar string
	 */
	const URL              = 'https://data.icecat.biz/export/freexml/refs/LanguageList.xml.gz';
    const XML_FILE_ARCHIVE = '/tmp/languages-list.xml.gz';
    const XML_FILE         = '/tmp/languages-list.xml';
    
    /**
     * (non-PHPdoc)
     * @see \Strixos\DataFlowBundle\Model\EtlService\AbstractService::process()
     */
    public function process()
    {
        $extract = new LanguagesExtract();
        $extract->process();
        
        // TODO Call Transformer and Loader
    }
}