<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractOrmEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="flexibleentity_entity_attribute_option_value")
 * @ORM\Entity
 */
class OrmEntityAttributeOptionValue extends AbstractOrmEntityAttributeOptionValue
{

    /**
     * Overrided to change target option name
     *
     * @var OrmEntityAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="OrmEntityAttributeOption", inversedBy="optionValues")
     * @ORM\JoinColumn(name="option_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $option;

}
