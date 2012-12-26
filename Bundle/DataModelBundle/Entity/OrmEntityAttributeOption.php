<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeOption;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute options
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="entity_attribute_option")
 * @ORM\Entity
 */
class OrmEntityAttributeOption extends AbstractOrmEntityAttributeOption
{

    /**
     * Overrided to change target entity name
     *
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="OrmEntityAttribute")
     * @ORM\JoinColumn(name="attribute_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $attribute;

    /**
     * @var ArrayCollection $values
     *
     * @ORM\OneToMany(targetEntity="OrmEntityAttributeOptionValue", mappedBy="option", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $values;
}
