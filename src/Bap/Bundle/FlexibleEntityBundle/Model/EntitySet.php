<?php
namespace Bap\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity type, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
abstract class EntitySet
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $code
     */
    protected $code;

    /**
    * @var string $title
    */
    protected $title;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     * 
     * @return EntitySet
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     * 
     * @return EntitySet
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add group
     *
     * @param EntityGroup $group
     * 
     * @return EntitySet
     */
    public function addGroup(EntityGroup $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param EntityGroup $group
     */
    public function removeGroup(EntityGroup $group)
    {
        $this->groups->removeElement($group);
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

    /**
     * Get group
     *
     * @param string $code
     * 
     * @return EntityGroup
     */
    public function getGroup($code)
    {
        foreach ($this->groups as $group) {
            if ($group->getCode() == $code) {
                return $group;
            }
        }

        return false;
    }
}
