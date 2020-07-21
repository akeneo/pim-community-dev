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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class ProductIdentifier
{
    /** @var string */
    private $identifier;

    public function __construct(string $identifier)
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException('A product identifier cannot be empty');
        }

        $this->identifier = $identifier;
    }

    public function __toString()
    {
        return $this->identifier;
    }
}
