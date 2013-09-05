<?php

namespace Oro\Bundle\ConfigBundle\Entity;

use JMS\Serializer\Annotation\Exclude;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConfigValue
 *
 * @ORM\Table(
 *  name="oro_config_value",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="UQ_ENTITY", columns={"field_id", "scope", "config_id"})}
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
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key", type="string", length=255)
     */
    protected $key;

    /**
     * @var Config[]
     *
     * @ORM\ManyToOne(targetEntity="Config", inversedBy="values")
     * @ORM\JoinColumn(name="config_id", referencedColumnName="id")
     * @Exclude
     */
    protected $config;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $section;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;


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
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set config
     *
     * @param string $config
     * @return Config
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
