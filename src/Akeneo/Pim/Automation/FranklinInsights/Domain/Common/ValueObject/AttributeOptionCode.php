<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class AttributeOptionCode
{
    /** @var string */
    private $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Attribute option code cannot be an empty string');
        }

        $this->code = $code;
    }

    public function equals(AttributeOptionCode $attributeOptionCode): bool
    {
        return $this->code === (string) $attributeOptionCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->code;
    }
}
