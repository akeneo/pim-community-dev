<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity
 * @ORM\Table
 * @Oro\Loggable
 */
class LoggableCollectionClass
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }
}
