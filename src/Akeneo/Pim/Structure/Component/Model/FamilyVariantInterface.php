<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyVariantInterface extends TranslatableInterface
{
    /**
     * @return null|int
     */
    public function getId(): ?int;

    /**
     * @return null|string
     */
    public function getCode(): ?string;

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
     * @return null|VariantAttributeSetInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getVariantAttributeSet(int $level): ?VariantAttributeSetInterface;

    /**
     * This method is needed for the validation of the variant attribute sets.
     * It is performed from the family variant, with the option "traversable: true".
     *
     * @return Collection
     */
    public function getVariantAttributeSets(): Collection;

    /**
     * @return Collection
     */
    public function getAttributes(): Collection;

    /**
     * @return Collection
     */
    public function getAxes(): Collection;

    /**
     * @param VariantAttributeSetInterface $variantAttributeSet
     */
    public function addVariantAttributeSet(VariantAttributeSetInterface $variantAttributeSet): void;

    /**
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family): void;

    /**
     * @return null|FamilyInterface
     */
    public function getFamily(): ?FamilyInterface;

    /**
     * @return int
     */
    public function getNumberOfLevel(): int;

    /**
     * Returns the variant attribute set level in which a given attribute is located.
     *
     * @param string $attributeCode
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function getLevelForAttributeCode(string $attributeCode): int;

    /**
     * Get available axes attribute types
     *
     * @return array
     */
    public static function getAvailableAxesAttributeTypes(): array;
}
