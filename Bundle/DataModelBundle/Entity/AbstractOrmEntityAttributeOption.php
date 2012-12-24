<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractOrmEntityAttributeOption extends AbstractEntityAttributeOption
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
     * Used locale to override Translation listener(s locale
     * this is not a mapped attribute of entity metadata, just a simple property
     */
    protected $locale;

    /**
     * Set attribute
     *
     * @param AbstractOrmEntityAttribute $attribute
     *
     * @return EntityAttributeOption
     */
    public function setAttribute(AbstractOrmEntityAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }
}
