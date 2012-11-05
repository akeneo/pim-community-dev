<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product field group (general, media, seo, etc)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Group")
 * @ORM\Entity
 */
class ProductGroup
{
    /**
     * @var integer $_id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $_code
     *
     * @ORM\Column(name="code", type="string")
     */
    private $code;

    /**
     * @var Type $type
     *
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="groups")
     */
    protected $type;

    /**
     * @var ArrayCollection $fields
     * @ORM\ManyToMany(targetEntity="ProductField", cascade={"persist"})
     * @ORM\JoinTable(name="Akeneo_PimCatalog_Product_Group_Field")
     */
    private $fields;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set code
     *
     * @param string $code
     * @return ProductGroup
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set type
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductType $type
     * @return ProductGroup
     */
    public function setType(\Pim\Bundle\CatalogBundle\Entity\ProductType $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return Pim\Bundle\CatalogBundle\Entity\ProductType 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add fields
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductField $fields
     * @return ProductGroup
     */
    public function addField(\Pim\Bundle\CatalogBundle\Entity\ProductField $fields)
    {
        $this->fields[] = $fields;
    
        return $this;
    }

    /**
     * Remove fields
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductField $fields
     */
    public function removeField(\Pim\Bundle\CatalogBundle\Entity\ProductField $fields)
    {
        $this->fields->removeElement($fields);
    }

    /**
     * Get fields
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFields()
    {
        return $this->fields;
    }
}