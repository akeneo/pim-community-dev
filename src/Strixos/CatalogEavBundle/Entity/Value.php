<?php

namespace Strixos\CatalogEavBundle\Entity;

use Bap\FlexibleEntityBundle\Model\EntityField;

use Doctrine\ORM\Mapping as ORM;
use Bap\FlexibleEntityBundle\Model\Entity;

/**
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="StrixosCatalogEav_Product_Value")
 * @ORM\Entity
 */
class Value
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Entity $product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="values")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
    * @var Field $field
    *
    * @ORM\ManyToOne(targetEntity="Field")
    */
    protected $field;

    /**
     * TODO : basic sample for basic EAV implementation, only varchar values
     * @var string $content
     *
     * @ORM\Column(name="content", type="string", length=255, unique=true)
     */
    private $content;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Value
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set product
     *
     * @param Strixos\CatalogEavBundle\Entity\Product $product
     * @return Value
     */
    public function setProduct(\Strixos\CatalogEavBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Strixos\CatalogEavBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set field
     *
     * @param Strixos\CatalogEavBundle\Entity\Field $field
     * @return Value
     */
    public function setField(\Strixos\CatalogEavBundle\Entity\Field $field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return Strixos\CatalogEavBundle\Entity\Field
     */
    public function getField()
    {
        return $this->field;
    }
}