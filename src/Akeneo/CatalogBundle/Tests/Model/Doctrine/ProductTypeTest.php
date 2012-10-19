<?php
namespace Akeneo\CatalogBundle\Tests\Model;

use \PHPUnit_Framework_TestCase;
use Akeneo\CatalogBundle\Tests\Model\AbtractProductTypeTest;

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
    protected $modelType   = 'Akeneo\CatalogBundle\Entity\ProductTypeManager';
    protected $modelEntity = 'Akeneo\CatalogBundle\Entity\ProductManager';
    protected $entityType  = 'Akeneo\CatalogBundle\Entity\ProductType';
    protected $entity      = 'Akeneo\CatalogBundle\Entity\ProductEntity';
    protected $entityGroup = 'Akeneo\CatalogBundle\Entity\ProductGroup';
    protected $entityField = 'Akeneo\CatalogBundle\Entity\ProductField';
}