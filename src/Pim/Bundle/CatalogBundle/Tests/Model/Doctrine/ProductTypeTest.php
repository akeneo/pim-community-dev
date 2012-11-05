<?php
namespace Pim\Bundle\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use Pim\Bundle\CatalogBundle\Tests\Model\AbtractProductTypeTest;

/**
 *
 * Aims to test product type model with doctrine implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeTest extends AbtractProductTypeTest
{
    protected $serviceName = 'akeneo.catalog.model_producttype_doctrine';
    protected $modelType   = 'Pim\Bundle\CatalogBundle\Entity\ProductTypeManager';
    protected $modelEntity = 'Pim\Bundle\CatalogBundle\Entity\ProductManager';
    protected $entityType  = 'Pim\Bundle\CatalogBundle\Entity\ProductType';
    protected $entity      = 'Pim\Bundle\CatalogBundle\Entity\ProductEntity';
    protected $entityGroup = 'Pim\Bundle\CatalogBundle\Entity\ProductGroup';
    protected $entityField = 'Pim\Bundle\CatalogBundle\Entity\ProductField';
}