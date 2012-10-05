<?php
namespace Akeneo\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product field group (general, media, seo, etc)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="AkeneoCatalog_Product_Group")
 * @ORM\Entity
 */
class Group
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
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="groups")
     */
    protected $type;

    /**
     * @var ArrayCollection $fields
     * @ORM\ManyToMany(targetEntity="Field", cascade={"persist"})
     * @ORM\JoinTable(name="AkeneoCatalog_Product_Group_Field")
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
     * @return Group
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
     * @param Akeneo\CatalogBundle\Entity\Type $type
     * @return Group
     */
    public function setType(\Akeneo\CatalogBundle\Entity\Type $type = null)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return Akeneo\CatalogBundle\Entity\Type 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add fields
     *
     * @param Akeneo\CatalogBundle\Entity\Field $fields
     * @return Group
     */
    public function addField(\Akeneo\CatalogBundle\Entity\Field $fields)
    {
        $this->fields[] = $fields;
    
        return $this;
    }

    /**
     * Remove fields
     *
     * @param Akeneo\CatalogBundle\Entity\Field $fields
     */
    public function removeField(\Akeneo\CatalogBundle\Entity\Field $fields)
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