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
final class FamilyCode
{
    /** @var string */
    private $familyCode;

    /**
     * @param string $familyCode
     */
    public function __construct(string $familyCode)
    {
        if (empty($familyCode)) {
            throw new \InvalidArgumentException('Family code cannot be an empty string');
        }

        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->familyCode;
    }
}
