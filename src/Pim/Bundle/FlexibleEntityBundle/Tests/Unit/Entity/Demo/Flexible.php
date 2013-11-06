<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * A concret flexible class
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 */
class Flexible extends AbstractEntityFlexible
{
    /**
     * @var string $myfield
     */
    protected $myfield;

    /**
     * @var Value
     * @ORM\OneToMany(targetEntity="FlexibleValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Get myfield
     *
     * @return string
     */
    public function getMyfield()
    {
        return $this->myfield;
    }

    /**
     * Set myfield
     *
     * @param string $myfield
     *
     * @return Flexible
     */
    public function setMyfield($myfield)
    {
        $this->myfield = $myfield;

        return $this;
    }
}
