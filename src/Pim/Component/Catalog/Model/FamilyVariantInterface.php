<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
    public function setCode(string $code);

    /**
     * @return AttributeSetInterface
     */
    public function getCommonAttributeSet(): AttributeSetInterface;

    /**
     * @param int $level
     *
     * @return AttributeSetInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getVariantAttributeSet(int $level): AttributeSetInterface;

    /**
     * @return ArrayCollection
     */
    public function getAttributes(): ArrayCollection;

    /**
     * @return ArrayCollection
     */
    public function getAxes(): ArrayCollection;

    /**
     * @param int                   $level
     * @param AttributeSetInterface $variantAttributeSets
     *
     * @throws \InvalidArgumentException
     */
    public function addVariantAttributeSet(int $level, AttributeSetInterface $variantAttributeSets): void;

    /**
     * @param AttributeSetInterface $variantAttributeSets
     *
     * @return mixed
     */
    public function addCommonAttributeSet(AttributeSetInterface $variantAttributeSets): void;

    /**
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family): void;

    /**
     * @return FamilyInterface
     */
    public function getFamily(): FamilyInterface;
}
