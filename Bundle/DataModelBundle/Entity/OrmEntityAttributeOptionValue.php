<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="entity_attribute_option_value")
 * @ORM\Entity
 */
class OrmEntityAttributeOptionValue extends AbstractOrmEntityAttributeOptionValue
{

    /**
     * Overrided to change target option name
     *
     * @var OrmEntityAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="OrmEntityAttributeOption")
     * @ORM\JoinColumn(name="option_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $option;

}
