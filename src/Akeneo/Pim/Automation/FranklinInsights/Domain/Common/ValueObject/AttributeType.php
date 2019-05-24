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

class AttributeType
{
    /** @var string */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (empty($type)) {
            throw new \InvalidArgumentException('Attribute type cannot be an empty string');
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->type;
    }
}
