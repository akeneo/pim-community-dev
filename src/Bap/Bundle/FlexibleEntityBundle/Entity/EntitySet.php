<?php
namespace Bap\Bundle\FlexibleEntityBundle\Entity;

use Bap\Bundle\FlexibleEntityBundle\Model\EntitySet as AbstractEntitySet;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityGroup as AbstractEntityGroup;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base Doctrine ORM entity set
 * 
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntitySet extends AbstractEntitySet
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
     * @ORM\OneToMany(targetEntity="EntityGroup", mappedBy="set", cascade={"persist", "remove"})
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
     * @param AbstractEntityGroup $group
     * 
     * @return AbstractEntitySet
     */
    public function addGroup(AbstractEntityGroup $group)
    {
        $this->groups[] = $group;
        $group->setSet($this);

        return $this;
    }

    /**
     * Custom remove group method to ensure group is removed without explicit call (due to oneToMany)
     *
     * @param AbstractEntityGroup $group
     */
    public function removeGroup(AbstractEntityGroup $group)
    {
        $this->groups->removeElement($group);
        $group->setSet(null);
    }

}
