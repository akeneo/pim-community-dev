<?php

namespace Oro\Bundle\UserBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use JMS\Serializer\Annotation\Exclude;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 */
class UserSoap extends User
{
    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @Soap\ComplexType("string")
     */
    protected $username;

    /**
     * @Soap\ComplexType("string")
     */
    protected $email;

    /**
     * @Soap\ComplexType("string")
     */
    protected $firstName;

    /**
     * @Soap\ComplexType("string")
     */
    protected $lastName;

    /**
     * @Exclude
     */
    protected $enabled = true;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $plainPassword;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $lastLogin;

    /**
     * @Exclude
     */
    protected $roles;

    /**
     * @Soap\ComplexType("int[]")
     */
    protected $rolesCollection;

    /**
     * @Soap\ComplexType("int[]", nillable=true)
     */
    protected $groups;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $owner;

    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]", nillable=true)
     */
    protected $attributes;

    public function setRolesCollection($collection)
    {
        $this->rolesCollection = $collection;
    }

    public function getRolesCollection()
    {
        return $this->rolesCollection;
    }

    /**
     * @return \Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
