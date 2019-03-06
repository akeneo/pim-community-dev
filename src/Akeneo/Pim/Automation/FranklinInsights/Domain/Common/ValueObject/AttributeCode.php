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
    private $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Attribute code cannot be an empty string');
        }

        $this->code = $code;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->code;
    }

    public function equals(AttributeCode $attributeCode): bool
    {
        return $this->code === (string) $attributeCode;
    }
}
