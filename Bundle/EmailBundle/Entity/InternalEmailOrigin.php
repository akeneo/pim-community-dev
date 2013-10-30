<?php

namespace Oro\Bundle\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * An Email Origin which cam be used for emails sent by BAP
 *
 * @ORM\Entity
 */
class InternalEmailOrigin extends EmailOrigin
{
    const BAP = 'BAP';

    /**
     * @var string
     *
     * @ORM\Column(name="internal_name", type="string", length=30)
     * @Soap\ComplexType("string")
     * @Type("string")
     */
    protected $name;

    /**
     * Get an internal email origin name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set an internal email origin name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get a human-readable representation of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('InternalEmailOrigin(%s)', $this->name);
    }
}
