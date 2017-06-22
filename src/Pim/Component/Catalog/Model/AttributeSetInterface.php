<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeSetInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection;

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes);

    /**
     * @return ArrayCollection
     */
    public function getAxes(): ArrayCollection;

    /**
     * @param array $axes
     */
    public function setAxes(array $axes);
}
