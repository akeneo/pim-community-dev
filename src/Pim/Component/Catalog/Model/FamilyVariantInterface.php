<?php

namespace Pim\Component\Catalog\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyVariantInterface
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
     * @return ArrayCollection
     */
    public function getVariantAttributeSets(): ArrayCollection;

    /**
     * @param ArrayCollection $variantAttributeSets
     */
    public function setVariantAttributeSets(ArrayCollection $variantAttributeSets);
}
