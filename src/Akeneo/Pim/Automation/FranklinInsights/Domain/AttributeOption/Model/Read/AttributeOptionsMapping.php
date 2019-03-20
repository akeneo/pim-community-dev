<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class AttributeOptionsMapping
{
    /** @var FamilyCode */
    private $familyCode;

    /** @var string */
    private $franklinAttributeId;

    /** @var AttributeOptionMapping[] */
    private $mapping;

    public function __construct(FamilyCode $familyCode, string $franklinAttributeId, array $mapping = [])
    {
        $this->familyCode = $familyCode;
        $this->franklinAttributeId = $franklinAttributeId;
        $this->mapping = $mapping;
        usort($this->mapping, function ($a, $b) {
            return $a->franklinAttributeOptionLabel() <=> $b->franklinAttributeOptionLabel();
        });
    }

    /**
     * @return FamilyCode
     */
    public function familyCode(): FamilyCode
    {
        return $this->familyCode;
    }

    /**
     * @return string
     */
    public function franklinAttributeId(): string
    {
        return $this->franklinAttributeId;
    }

    /**
     * @return AttributeOptionMapping[]
     */
    public function mapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param string $attributeOptionCode
     *
     * @return bool
     */
    public function hasPimAttributeOption(string $attributeOptionCode): bool
    {
        foreach ($this->mapping as $attributeOption) {
            if ($attributeOption->catalogAttributeOptionCode() === $attributeOptionCode) {
                return true;
            }
        }

        return false;
    }
}
