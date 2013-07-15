<?php

namespace Oro\Bundle\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Group;

/**
 * EmailNotification
 *
 * @ORM\Table("oro_notification_recipient_list")
 * @ORM\Entity()
 */
class RecipientList
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var User[]
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="oro_notification_recipient_user",
     *      joinColumns={@ORM\JoinColumn(name="recipient_list_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $users;

    /**
     * @var Group[]
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\Group")
     * @ORM\JoinTable(name="oro_notification_recipient_group",
     *      joinColumns={@ORM\JoinColumn(name="recipient_list_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $groups;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var boolean
     * @ORM\Column(name="owner", type="boolean", nullable=true)
     */
    protected $owner;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Getter for email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set groups
     *
     * @param Group[] $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Getter for g
     *
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }


    /**
     * Setter for users
     *
     * @param User[] $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * Getters for users
     *
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Setter for owner field
     *
     * @param boolean $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Getter for owner field
     *
     * @return boolean
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * To string implementation
     *
     * @return string
     */
    public function __toString()
    {
        switch(true) {
            case $this->getEmail():
                $result = 'Email: ' . $this->getEmail();
                break;
            case count($this->getGroups()):
                $result = 'List of groups';
                break;
            case count($this->getUsers()):
                $result = 'List of users';
                break;
            case $this->getOwner():
                $result = 'Entity owner';
                break;
            default:
                $result = '';
                break;
        }

        return $result;
    }
}
