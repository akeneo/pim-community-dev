<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityValue as AbstractEntityValue;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product value as Embedded Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\EmbeddedDocument
 */
class ProductValue extends AbstractEntityValue
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\ReferenceOne(targetDocument="ProductField", simple=true)
     */
    protected $field;

    /**
    * @MongoDB\String
    */
    protected $data;

}
