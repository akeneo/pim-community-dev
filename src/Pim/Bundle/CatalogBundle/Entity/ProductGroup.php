<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\FlexibleEntityBundle\Entity\EntityGroup as AbstractEntityGroup;

/**
 * Product attribute group (general, media, seo, etc)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Group")
 * @ORM\Entity
 */
class ProductGroup extends AbstractEntityGroup
{

    /**
     * @var Set set
     *
     * @ORM\ManyToOne(targetEntity="ProductSet", inversedBy="groups")
     */
    protected $set;

    /**
     * @var ArrayCollection $attributes
     * @ORM\ManyToMany(targetEntity="ProductAttribute")
     * @ORM\JoinTable(name="Akeneo_PimCatalog_Product_Group_Attribute")
     */
    protected $attributes = array();

}
