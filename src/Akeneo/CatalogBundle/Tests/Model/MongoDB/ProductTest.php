<?php
namespace Akeneo\CatalogBundle\Tests\Model\MongoDB;

use \PHPUnit_Framework_TestCase;
use Akeneo\CatalogBundle\Tests\Model\AbtractProductTest;

/**
 *
 * Aims to test product model (mongo document impl)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTest extends AbtractProductTest
{
    protected $productManagerName     = 'akeneo.catalog.model_product_mongo';
    protected $productTypeManagerName = 'akeneo.catalog.model_producttype_mongo';

    protected $productClass = 'Akeneo\CatalogBundle\Document\ProductMongo';
    protected $typeClass    = 'Akeneo\CatalogBundle\Document\ProductTypeMongo';
    protected $fieldClass   = 'Akeneo\CatalogBundle\Document\ProductFieldMongo';

}