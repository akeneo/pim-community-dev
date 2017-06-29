<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyVariantInterface extends TranslatableInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     */
    public function setCode(string $code): void;

    /**
     * @return CommonAttributeCollection
     */
    public function getCommonAttributes(): CommonAttributeCollection;

    /**
     * @param int $level
     *
     * @return VariantAttributeSetInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getVariantAttributeSet(int $level): VariantAttributeSetInterface;

    /**
     * @return Collection
     */
    public function getAttributes(): Collection;

    /**
     * @return Collection
     */
    public function getAxes(): Collection;

    /**
     * @param int                          $level
     * @param VariantAttributeSetInterface $variantAttributeSet
     *
     * @throws \InvalidArgumentException
     */
    public function addVariantAttributeSet(int $level, VariantAttributeSetInterface $variantAttributeSet): void;

    /**
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family): void;

    /**
     * @return FamilyInterface
     */
    public function getFamily(): FamilyInterface;

    /**
     * @return int
     */
    public function getLevel(): int;
}
