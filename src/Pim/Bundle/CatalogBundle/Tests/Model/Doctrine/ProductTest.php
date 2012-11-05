<?php
namespace Pim\Bundle\CatalogBundle\Tests\Model\Doctrine;

use \PHPUnit_Framework_TestCase;
use Pim\Bundle\CatalogBundle\Tests\Model\AbtractProductTest;

/**
 *
 * Aims to test product model (doctrine entity impl)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTest extends AbtractProductTest
{
    protected $productManagerName     = 'akeneo.catalog.model_product_doctrine';
    protected $productTypeManagerName = 'akeneo.catalog.model_producttype_doctrine';

    protected $productClass = 'Pim\Bundle\CatalogBundle\Entity\ProductEntity';
    protected $typeClass    = 'Pim\Bundle\CatalogBundle\Entity\ProductType';
    protected $fieldClass   = 'Pim\Bundle\CatalogBundle\Entity\ProductField';

}