<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttribute as AbstractEntityAttribute;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Product type attribute as document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 * @GRID\Source(columns="id, code, title, type")
 */
class ProductAttribute extends AbstractEntityAttribute
{

    /**
     * @MongoDB\Id
     * @GRID\Column()
     */
    protected $id;

    /**
     * @MongoDB\String
     * @GRID\Column()
     */
    protected $code;

    /**
    * @MongoDB\String
    * @GRID\Column()
    */
    protected $title;

    /**
     * TODO define custom attribute type ?
     * @MongoDB\String
     * @GRID\Column()
     */
    protected $type;

    /**
     * @MongoDB\EmbedMany(targetDocument="ProductAttributeOption")
     */
    protected $options = array();

    /**
     * @MongoDB\Boolean
     */
    protected $uniqueValue;

    /**
     * @MongoDB\Boolean
     */
    protected $valueRequired;

    /**
     * @MongoDB\Boolean
     */
    protected $searchable;

    /**
     * @MongoDB\Int
     */
    protected $scope;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

}
