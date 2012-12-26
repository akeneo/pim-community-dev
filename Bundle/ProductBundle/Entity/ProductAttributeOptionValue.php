<?php
namespace Oro\Bundle\ProductBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\AbstractOrmEntityAttributeOptionValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute option values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="product_attribute_option_value")
 * @ORM\Entity
 */
class ProductAttributeOptionValue extends AbstractOrmEntityAttributeOptionValue
{

    /**
     * Overrided to change target option name
     *
     * @var ProductAttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="ProductAttributeOption")
     * @ORM\JoinColumn(name="option_id", nullable=false, onDelete="CASCADE", referencedColumnName="id")
     */
    protected $option;

}
