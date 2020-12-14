<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyId
{
    private int $id;

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Family id should be a positive integer');
        }

        $this->id = $id;
    }

    public function toInt(): int
    {
        return $this->id;
    }

    public function __toString()
    {
        return strval($this->id);
    }
}
