<?php
namespace Strixos\IcecatConnectorBundle\Model\Service;

use Strixos\IcecatConnectorBundle\Model\Extract\ProductsExtract;

use Strixos\DataFlowBundle\Model\Service\AbstractService;

/**
 * 
 * Service ETL to import products list from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductsService extends AbstractService
{
	/**
	 * @staticvar string
	 */
    const URL              = 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz';
    const XML_FILE_ARCHIVE = '/tmp/suppliers-list.xml.gz';
    const XML_FILE         = '/tmp/suppliers-list.xml';
}