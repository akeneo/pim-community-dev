<?php
namespace Akeneo\CatalogBundle\Tests\Model\MongoDB;

use \PHPUnit_Framework_TestCase;
use Akeneo\CatalogBundle\Tests\Model\AbtractProductTypeTest;

/**
 *
 * Aims to test product type model with mongo implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeTest extends AbtractProductTypeTest
{
    protected $serviceName = 'akeneo.catalog.model_producttype_mongo';
    protected $modelType   = 'Akeneo\CatalogBundle\Model\MongoDB\ProductType';
    protected $modelEntity = 'Akeneo\CatalogBundle\Model\MongoDB\Product';
    protected $entityType  = 'Akeneo\CatalogBundle\Document\ProductTypeMongo';
    protected $entityGroup = 'Akeneo\CatalogBundle\Document\ProductGroupMongo';
    protected $entityField = 'Akeneo\CatalogBundle\Document\ProductFieldMongo';
}