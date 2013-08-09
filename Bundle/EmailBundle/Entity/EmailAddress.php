<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

/**
 * Email Address
 * This class is dynamically extended based of email owner providers.
 * For details see
 *   - Resources/cache/Entity/EmailAddress.php.twig
 *   - Cache/EmailAddressCacheWarmer.php
 *   - Cache/EmailAddressCacheClearer.php
 *   - Entity/Provider/EmailOwnerProviderStorage.php
 *   - DependencyInjection/Compiler/EmailOwnerConfigurationPass.php
 *   - OroEmailBundle.php
 *
 * @ORM\MappedSuperclass
 */
class EmailAddress
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     * @Type("dateTime")
     */
    protected $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     * @Type("dateTime")
     */
    protected $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Type("string")
     */
    protected $email;

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
     * Get entity created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get entity updated date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Get email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email address.
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email owner
     *
     * @return EmailOwnerInterface
     */
    public function getOwner()
    {
        return null;
    }

    /**
     * Set email owner
     *
     * @param EmailOwnerInterface|null $owner
     * @return $this
     */
    public function setOwner(EmailOwnerInterface $owner = null)
    {
        return $this;
    }
}
