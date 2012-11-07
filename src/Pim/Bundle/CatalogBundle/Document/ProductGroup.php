<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup as AbstractEntityGroup;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product group as Embedded Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\EmbeddedDocument
 */
class ProductGroup extends AbstractEntityGroup
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $code;

    /**
     * @MongoDB\String
     */
    protected $title;

    /**
     * @MongoDB\ReferenceMany(targetDocument="ProductField", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $fields = array();

    /**
    * Constructor
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }
}
