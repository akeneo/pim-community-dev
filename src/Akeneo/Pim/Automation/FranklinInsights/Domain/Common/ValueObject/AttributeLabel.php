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


class AttributeLabel
{
    /** @var string */
    private $label;

    /**
     * @param string $label
     */
    public function __construct(string $label)
    {
        if (empty($label)) {
            throw new \InvalidArgumentException('Attribute label cannot be an empty string');
        }

        $this->label = $label;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->label;
    }
}
