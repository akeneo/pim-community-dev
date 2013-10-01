<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="oro_session")
 * @ORM\Entity
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sess_data", type="text", nullable=false)
     */
    protected $value;

    /**
     * @var int
     *
     * @ORM\Column(name="sess_time", type="integer", nullable=false)
     */
    protected $time;
}
