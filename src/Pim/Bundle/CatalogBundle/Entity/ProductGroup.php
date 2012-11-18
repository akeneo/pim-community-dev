<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup as AbstractEntityGroup;

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
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string")
     */
    protected $code;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set Set
     *
     * @param  ProductSet   $set
     * @return ProductGroup
     */
    public function setSet(ProductSet $set = null)
    {
        $this->set = $set;

        return $this;
    }
}
