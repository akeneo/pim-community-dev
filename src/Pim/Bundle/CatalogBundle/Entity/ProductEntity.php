<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Entity\Entity as AbstractEntity;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttributeValue as AbstractEntityAttributeValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flexible product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Entity")
 * @ORM\Entity
 */
class ProductEntity extends AbstractEntity
{

    /**
     * @var EntitySet $set
     *
     * @ORM\ManyToOne(targetEntity="ProductSet")
     */
    protected $set;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

}
