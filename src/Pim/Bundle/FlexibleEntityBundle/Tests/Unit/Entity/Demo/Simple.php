<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo;

use Doctrine\ORM\Mapping as ORM;

/**
 * Simple entity
 *
 * @ORM\Entity()
 */
class Simple
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

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
     * Set name
     *
     * @param string $name
     *
     * @return Manufacturer
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
}
