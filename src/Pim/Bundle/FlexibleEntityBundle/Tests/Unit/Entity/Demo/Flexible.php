<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo;

use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Doctrine\ORM\Mapping as ORM;

/**
 * A concret flexible class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
