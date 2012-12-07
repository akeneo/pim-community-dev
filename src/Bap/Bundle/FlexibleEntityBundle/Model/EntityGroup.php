<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttribute;
/**
 * Abstract entity group, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityGroup
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
    * @var string $title
    */
    protected $title;

    /**
     * @var ArrayCollection $attributes
     */
    protected $attributes = array();

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
     * Set id
     *
     * @param integer $id
     *
     * @return EntityGroup
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * Set code
     *
     * @param string $code
     *
     * @return EntitySet
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return EntitySet
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Add attribute
     *
     * @param EntityAttribute $attribute
     *
     * @return ProductGroup
     */
    public function addAttribute(EntityAttribute $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param EntityAttribute $attribute
     */
    public function removeAttribute(EntityAttribute $attribute)
    {
        $this->attributes->removeElement($attribute);
    }

    /**
     * Get attribute
     *
     * @param string $code
     *
     * @return EntityAttribute
     */
    public function getAttribute($code)
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode() == $code) {
                return $attribute;
            }
        }

        return false;
    }

    /**
     * Get attributes
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

}
