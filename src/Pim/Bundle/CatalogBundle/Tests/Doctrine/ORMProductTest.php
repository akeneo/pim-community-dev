<?php
namespace Pim\Bundle\CatalogBundle\Tests\Doctrine;

/**
 * Provide abstract test for product model (can be used for different implementation)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ORMProductTest extends AbtractProductTest
{
    protected $objectManagerName = 'doctrine.orm.entity_manager';
}
