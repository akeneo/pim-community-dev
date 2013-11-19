<?php

namespace Oro\Bundle\UserBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use JMS\Serializer\Annotation\Exclude;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass()
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
    protected $namePrefix;

    /**
     * @Soap\ComplexType("string")
     */
    protected $firstName;

    /**
     * @Soap\ComplexType("string")
     */
    protected $middleName;

    /**
     * @Soap\ComplexType("string")
     */
    protected $lastName;

    /**
     * @Soap\ComplexType("string")
     */
    protected $nameSuffix;

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


    public function setRolesCollection($collection)
    {
        $this->rolesCollection = $collection;
    }

    public function getRolesCollection()
    {
        return $this->rolesCollection;
    }
}
