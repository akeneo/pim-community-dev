<?php
namespace Strixos\IcecatConnectorBundle\Model\Service;

use Strixos\IcecatConnectorBundle\Model\Extract\ProductExtract;

use Strixos\DataFlowBundle\Model\Service\AbstractService;

/**
 * 
 * Service ETL to import product data from icecat
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductService extends AbstractService
{
    /**
     * @staticvar string
     */
    const BASE_URL         = 'http://data.Icecat.biz/xml_s3/xml_server3.cgi';
    const XML_FILE_ARCHIVE = '/tmp/suppliers-list.xml.gz';
    const XML_FILE         = '/tmp/suppliers-list.xml';
}