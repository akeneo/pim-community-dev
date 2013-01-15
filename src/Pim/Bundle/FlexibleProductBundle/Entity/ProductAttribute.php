<?php
namespace Pim\Bundle\FlexibleProductBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Custom properties for a product attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_flexibleproduct_product_attribute")
 * @ORM\Entity
 */
class ProductAttribute extends AbstractEntityFlexibleAttribute
{
    /**
     * @var Oro\Bundle\FlexibleEntityBundle\Entity\Attribute $attribute
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @ORM\Column(name="is_smart", type="boolean")
     */
    protected $smart;

    /**
     * Get name
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get description
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get searchable
     *
     * @return boolean $smart
     */
    public function getSmart()
    {
        return $this->smart;
    }

    /**
     * Set smart
     *
     * @param boolean $smart
     *
     * @return ProductAttribute
     */
    public function setSmart($smart)
    {
        $this->smart = $smart;

        return $this;
    }

}
