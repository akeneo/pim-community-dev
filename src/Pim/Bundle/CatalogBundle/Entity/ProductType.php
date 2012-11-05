<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntityType as AbstractEntityType;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Type")
 * @ORM\Entity
 */
class ProductType extends AbstractEntityType
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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
    * @var string $title
    *
    * @ORM\Column(name="title", type="string", length=255)
    */
    protected $title;

    /**
     * @var ArrayCollection $groups
     *
     * @ORM\OneToMany(targetEntity="ProductGroup", mappedBy="type", cascade={"persist", "remove"})
     */
    protected $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add groups
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductGroup $groups
     * @return ProductType
     */
    public function addGroup(\Pim\Bundle\CatalogBundle\Entity\ProductGroup $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param Pim\Bundle\CatalogBundle\Entity\ProductGroup $groups
     */
    public function removeGroup(\Pim\Bundle\CatalogBundle\Entity\ProductGroup $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

}