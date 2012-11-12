<?php
namespace Pim\Bundle\CatalogBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntitySet as AbstractEntitySet;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup as AbstractEntityGroup;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Table(name="Akeneo_PimCatalog_Product_Set")
 * @ORM\Entity
 */
class ProductSet extends AbstractEntitySet
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
     * @ORM\OneToMany(targetEntity="ProductGroup", mappedBy="set", cascade={"persist", "remove"})
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
     * Custom add group method to ensure group is added in type without explicit call (due to oneToMany)
     *
     * @param  AbstractEntityGroup $group
     * @return AbstractEntitySet
     */
    public function addGroup(AbstractEntityGroup $group)
    {
        $this->groups[] = $group;
        $group->setSet($this);
        return $this;
    }

}