<?php

namespace Oro\Bundle\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigValue
 *
 * @ORM\Table(
 *  name="oro_config_value",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="UQ_ENTITY", columns={"field", "scope"})}
 * )
 * @ORM\Entity(repositoryClass="Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository")
 */
class ConfigValue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="field", type="integer")
     */
    private $field;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=50)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;


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
     * Set field
     *
     * @param integer $field
     * @return ConfigValue
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return integer
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return ConfigValue
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return ConfigValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
