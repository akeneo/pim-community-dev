<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Exclude;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserBundle\Entity\Repository\AclRepository")
 * @ORM\Table(name="oro_user_acl", indexes={
 *      @ORM\Index(name="class_method_idx", columns={"class", "method"}),
 *      @ORM\Index(name="lft_idx", columns={"lft"}),
 *      @ORM\Index(name="lvl_idx", columns={"lvl"}),
 *      @ORM\Index(name="rgt_idx", columns={"rgt"}),
 *      @ORM\Index(name="root_idx", columns={"root"})
 * })
 */
class Acl
{
    const ROOT_NODE = 'root';

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50, name="id")
     * @Soap\ComplexType("string")
     */
    protected $id;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Acl", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     * @Exclude
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Acl", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     * @Exclude
     */
    protected $children;

    /**
     * @ORM\Column(type="string", length=250)
     * @Soap\ComplexType("string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=250)
     * @Soap\ComplexType("string")
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $class;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $method;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="aclResources")
     * @ORM\JoinTable(name="oro_user_acl_role",
     *      joinColumns={@ORM\JoinColumn(name="acl_id", referencedColumnName="id", onDelete="CASCADE")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     * @Exclude
     */
    protected $accessRoles;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @Exclude
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     * @Exclude
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @Exclude
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="string", length=50, nullable=true)
     * @Exclude
     */
    protected $root;

    public function __construct()
    {
        $this->accessRoles = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'class' => $this->getClass(),
            'method' => $this->getMethod(),
            'parent' => $this->getParent() ? $this->getParent()->getId() : ''
        );
    }

    /**
     * Set id
     *
     * @param  string $id
     * @return Acl
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Acl
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return Acl
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set acl info
     *
     * @param \Oro\Bundle\UserBundle\Annotation\Acl $aclData
     */
    public function setData(AnnotationAcl $aclData)
    {
        $this->setName($aclData->getName());
        $this->setClass($aclData->getClass());
        $this->setMethod($aclData->getMethod());
        if ($aclData->getDescription()) {
            $this->setDescription($aclData->getDescription());
        }
    }

    /**
     * Add children
     *
     * @param  Acl $children
     * @return Acl
     */
    public function addChildren(Acl $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Acl $children
     */
    public function removeChildren(Acl $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param  Acl $parent
     * @return Acl
     */
    public function setParent(Acl $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Acl
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set lft
     *
     * @param  integer $lft
     * @return Acl
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param  integer $lvl
     * @return Acl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param  integer $rgt
     * @return Acl
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param  string $root
     * @return Acl
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Get roles array for resource
     *
     * @return array
     */
    public function getAccessRolesNames()
    {
        $roles = array();
        /** @var $role Role */
        foreach ($this->accessRoles as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * Add accessRoles
     *
     * @param  Role $accessRoles
     * @return Acl
     */
    public function addAccessRole(Role $accessRoles)
    {
        $this->accessRoles[] = $accessRoles;

        return $this;
    }

    /**
     * Remove accessRole
     *
     * @param Role $accessRole
     */
    public function removeAccessRole(Role $accessRole)
    {
        $this->accessRoles->removeElement($accessRole);
    }

    /**
     * Get accessRoles
     *
     * @return Collection
     */
    public function getAccessRoles()
    {
        return $this->accessRoles;
    }

    /**
     * Set new access roles collection
     *
     * @param  Collection $roles
     * @return Acl
     */
    public function setAccessRoles(Collection $roles)
    {
        $this->accessRoles = $roles;

        return $this;
    }

    /**
     * Set class
     *
     * @param  string $class
     * @return Acl
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set method
     *
     * @param  string $method
     * @return Acl
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}
