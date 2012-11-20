<?php
namespace Bap\Bundle\FlexibleEntityBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeOption as AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntityAttributeOption extends AbstractEntityAttributeOption
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
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="EntityAttribute")
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @ORM\Column(name="data", type="string", length=255)
     */
    protected $value;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * Set attribute
     *
     * @param EntityAttribute $attribute
     * 
     * @return EntityAttributeOption
     */
    public function setAttribute(EntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }
}
