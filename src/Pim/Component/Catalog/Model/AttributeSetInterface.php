<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\Collection;

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
     * @return Collection
     */
    public function getAttributes(): Collection;

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes): void;

    /**
     * @return Collection
     */
    public function getAxes(): Collection;

    /**
     * @param array $axes
     */
    public function setAxes(array $axes): void;
}
