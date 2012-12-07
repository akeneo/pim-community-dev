<?php
namespace Pim\Bundle\CatalogBundle\Document;

use Oro\Bundle\FlexibleEntityBundle\Model\Entity as AbstractEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Product as Mongo Document
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @MongoDB\Document
 * @GRID\Source(columns="id, set.code")
 */
class ProductEntity extends AbstractEntity
{
    //@see https://doctrine-mongodb-odm.readthedocs.org/en/latest/reference/annotations-reference.html

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
    protected $sku;

    /**
     * @MongoDB\EmbedMany(targetDocument="ProductAttributeValue")
     */
    protected $values = array();

    /**
    * Constructor
    */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return EntityAttribute
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }
}
