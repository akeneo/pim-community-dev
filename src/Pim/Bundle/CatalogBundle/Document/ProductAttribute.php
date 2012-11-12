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
 * @MongoDB\Document(repositoryClass="Pim\Bundle\CatalogBundle\Document\ProductAttributeRepository")
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
     * @MongoDB\Index(unique=true)
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
     * @MongoDB\Boolean
     */
    protected $translatable;

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

    /**
     * Override to sort options
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        $sorted = array();
        // remove option
        foreach ($this->options as $key => $option) {
            $sorted[$option->getSortOrder()] = $option;
            $this->options->remove($key);
        }
        // sort and add with sorted index
        ksort($sorted);
        foreach ($sorted as $key => $option) {
            $this->options[$key] = $option;
        }
        return $this->options;
    }
}
