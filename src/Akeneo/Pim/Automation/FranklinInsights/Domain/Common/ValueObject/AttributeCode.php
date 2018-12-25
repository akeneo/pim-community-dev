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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class AttributeCode
{
    /** @var string */
    private $attributeCode;

    /**
     * @param string $attributeCode
     */
    public function __construct(string $attributeCode)
    {
        if (empty($attributeCode)) {
            throw new \InvalidArgumentException('Attribute code cannot be an empty string');
        }

        $this->attributeCode = $attributeCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->attributeCode;
    }
}
