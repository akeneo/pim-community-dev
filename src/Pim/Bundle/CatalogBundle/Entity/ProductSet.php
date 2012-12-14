<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Entity\EntitySet as AbstractEntitySet;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup as AbstractEntityGroup;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Product set class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="akeneo_catalog_product_set")
 * @ORM\Entity
 */
class ProductSet extends AbstractEntitySet
{

    /**
     * @var ArrayCollection $groups
     *
     * @ORM\OneToMany(targetEntity="ProductGroup", mappedBy="set", cascade={"persist", "remove"})
     */
    protected $groups;

}
