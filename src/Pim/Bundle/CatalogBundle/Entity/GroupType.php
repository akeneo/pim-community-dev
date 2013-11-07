<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
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
 * @Config(
 *  defaultValues={
 *      "entity"={"label"="Group type", "plural_label"="Group types"},
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 *
 * @ExclusionPolicy("all")
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
     * @ORM\Column(name="is_variant", type="boolean")
     */
    protected $variant;

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
     * Is variant
     *
     * @return boolean
     */
    public function isVariant()
    {
        return $this->variant;
    }

    /**
     * Set variant
     *
     * @param boolean $variant
     *
     * @return GroupType
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;

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
