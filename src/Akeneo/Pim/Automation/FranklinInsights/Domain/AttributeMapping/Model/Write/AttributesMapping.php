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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributesMapping
{
    /** @var string */
    private $familyCode;

    /** @var AttributeMapping[] */
    private $mapping;

    /**
     * @param string $familyCode
     */
    public function __construct(string $familyCode)
    {
        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function familyCode(): string
    {
        return $this->familyCode;
    }

    /**
     * @return AttributeMapping[]
     */
    public function mapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param string $franklinAttrId
     * @param string $franklinAttrType
     * @param AttributeInterface|null $pimAttribute
     *
     * @throws \Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException
     */
    public function map(string $franklinAttrId, string $franklinAttrType, ?AttributeInterface $pimAttribute): void
    {
        $this->mapping[] = new AttributeMapping($franklinAttrId, $franklinAttrType, $pimAttribute);
    }
}
