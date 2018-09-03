<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VariantAttributeSetInterface
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
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute): bool;

    /**
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute): void;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function setAttributes(array $attributes): void;

    /**
     * @return Collection
     */
    public function getAxes(): Collection;

    /**
     * @param AttributeInterface[] $axes
     */
    public function setAxes(array $axes): void;

    /**
     * @return int
     */
    public function getLevel(): int;

    /**
     * @param int $level
     */
    public function setLevel(int $level): void;

    /**
     * @param string $locale
     *
     * @return array
     */
    public function getAxesLabels(string $locale): array;
}
