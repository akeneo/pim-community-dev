<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Group type entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\CatalogBundle\Entity\Repository\GroupTypeRepository")
 * @ORM\Table(name="pim_catalog_group_type")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Group type", "plural_label"="Group types"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class GroupType
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
     * @ORM\Column(name="code", type="string", length=50, unique=true)
     */
    protected $code;

    /**
     * @var string $entity
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    protected $entity;

    /**
     * Get the id
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
     * @return string $code
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
     * @return GroupType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity
     *
     * @param string $entity
     *
     * @return GroupType
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Returns the code
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }
}
