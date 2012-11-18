<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeValue as AbstractEntityAttributeValue;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Product value as Embedded Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\EmbeddedDocument
 */
class ProductAttributeValue extends AbstractEntityAttributeValue
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="ProductAttribute", simple=true)
     */
    protected $attribute;

    /**
    * @MongoDB\String
    */
    protected $data;

}
