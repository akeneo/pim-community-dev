<?php

namespace Oro\Bundle\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * EmailNotification
 *
 * @ORM\Table("oro_notification_emailnotification")
 * @ORM\Entity(repositoryClass="Oro\Bundle\NotificationBundle\Entity\Repository\EmailNotificationRepository")
 */
class EmailNotification
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
     * @var string
     *
     * @ORM\Column(name="entity_name", type="string", length=255)
     */
    protected $entityName;

    /**
     * @var Event
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\NotificationBundle\Entity\Event",cascade={"persist"})
     */
    protected $event;

    /**
     * @var EmailTemplate
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\EmailBundle\Entity\EmailTemplate")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $template;

    /**
     * @var RecipientList
     *
     * @ORM\OneToOne(
     *     targetEntity="Oro\Bundle\NotificationBundle\Entity\RecipientList",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="recipient_list_id", referencedColumnName="id")
     */
    protected $recipientList;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $userOwner;

    /**
     * @var BusinessUnit[]
     *
     * @ORM\ManyToMany(targetEntity="\Oro\Bundle\OrganizationBundle\Entity\BusinessUnit")
     * @ORM\JoinTable(name="oro_owner_email_notification_business_unit",
     *      joinColumns={@ORM\JoinColumn(name="notification_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="business_unit_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $businessUnitOwners;

    /**
     * @var Organization[]
     *
     * @ORM\ManyToMany(targetEntity="\Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinTable(name="oro_owner_email_notification_organization",
     *      joinColumns={@ORM\JoinColumn(name="notification_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id",
     *      onDelete="CASCADE")}
     * )
     */
    protected $organizationOwners;

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
     * Set entityName
     *
     * @param string $entityName
     * @return EmailNotification
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    
        return $this;
    }

    /**
     * Get entityName
     *
     * @return string 
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set event
     *
     * @param Event $event
     * @return EmailNotification
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set template
     *
     * @param EmailTemplate $template
     * @return EmailNotification
     */
    public function setTemplate(EmailTemplate $template)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return EmailTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set recipient
     *
     * @param RecipientList $recipientList
     * @return EmailNotification
     */
    public function setRecipientList(RecipientList $recipientList)
    {
        $this->recipientList = $recipientList;
    
        return $this;
    }

    /**
     * Get recipient
     *
     * @return RecipientList
     */
    public function getRecipientList()
    {
        return $this->recipientList;
    }

    /**
     * Returns comma separated list
     *
     * @return string
     */
    public function getRecipientGroupsList()
    {
        if (!$this->getRecipientList()) {
            return '';
        }

        return implode(
            ', ',
            $this->getRecipientList()->getGroups()->map(
                function (Group $group) {
                    return $group->getName();
                }
            )->toArray()
        );
    }

    /**
     * Returns comma separated list
     *
     * @return string
     */
    public function getRecipientUsersList()
    {
        if (!$this->getRecipientList()) {
            return '';
        }

        return implode(
            ', ',
            $this->getRecipientList()->getUsers()->map(
                function (User $user) {
                    return sprintf('%s <%s>', $user->getFullname(), $user->getEmail());
                }
            )->toArray()
        );
    }

    /**
     * @return User
     */
    public function getUserOwner()
    {
        return $this->userOwner;
    }

    /**
     * @param User $userOwner
     * @return EmailNotification
     */
    public function setUserOwner(User $userOwner)
    {
        $this->userOwner = $userOwner;

        return $this;
    }

    /**
     * @return BusinessUnit[]
     */
    public function getBusinessUnitOwners()
    {
        return $this->businessUnitOwners;
    }

    /**
     * @param ArrayCollection $businessUnitOwners
     * @return EmailNotification
     */
    public function setBusinessUnitOwners($businessUnitOwners)
    {
        $this->businessUnitOwners = $businessUnitOwners;

        return $this;
    }

    /**
     * @return Organization[]
     */
    public function getOrganizationOwners()
    {
        return $this->organizationOwners;
    }

    /**
     * @param ArrayCollection $organizationOwners
     * @return EmailNotification
     */
    public function setOrganizationOwners($organizationOwners)
    {
        $this->organizationOwners = $organizationOwners;

        return $this;
    }
}
