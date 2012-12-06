<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Product attribute option as Embedded Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\EmbeddedDocument
 */
class ProductAttributeOption extends AbstractEntityAttributeOption
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     * @MongoDB\Index(sparse=true)
     */
    protected $value;

    /**
     * @MongoDB\Int
     */
    protected $sortOrder;

}
