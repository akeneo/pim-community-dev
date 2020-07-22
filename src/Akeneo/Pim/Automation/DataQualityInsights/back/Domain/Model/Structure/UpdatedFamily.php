<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure;

final class UpdatedFamily
{
    /** @var int */
    private $familyId;

    /** @var \DateTimeImmutable */
    private $updatedAt;

    public function __construct(int $familyId, \DateTimeImmutable $updatedAt)
    {
        $this->familyId = $familyId;
        $this->updatedAt = $updatedAt;
    }

    public function getFamilyId(): int
    {
        return $this->familyId;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
