<?php
namespace Pim\Bundle\CatalogBundle\Tests\Model\MongoDB;

use \PHPUnit_Framework_TestCase;
use Pim\Bundle\CatalogBundle\Tests\Model\AbtractProductTest;

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

    protected $productClass = 'Pim\Bundle\CatalogBundle\Document\ProductMongo';
    protected $typeClass    = 'Pim\Bundle\CatalogBundle\Document\ProductTypeMongo';
    protected $fieldClass   = 'Pim\Bundle\CatalogBundle\Document\ProductFieldMongo';

}