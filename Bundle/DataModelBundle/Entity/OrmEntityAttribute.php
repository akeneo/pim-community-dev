<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Oro\Bundle\DataModelBundle\Entity\Mapping\AbstractOrmEntityAttribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base entity attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @ORM\Table(name="entity_attribute", indexes={@ORM\Index(name="searchcode_idx", columns={"code"})} )
 * @ORM\Entity
 */
class OrmEntityAttribute extends AbstractOrmEntityAttribute
{

    /**
     * Overrided to change target entity name
     *
     * @var ArrayCollection $options
     *
     * @ORM\OneToMany(targetEntity="OrmEntityAttributeOption", mappedBy="attribute", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $options;

}
