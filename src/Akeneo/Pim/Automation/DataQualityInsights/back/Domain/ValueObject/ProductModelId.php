<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelId implements ProductEntityIdInterface
{
    /** @var int */
    private $id;

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('Product model id should be a positive integer');
        }

        $this->id = $id;
    }

    public static function fromString(string $id): self
    {
        return new self(intval($id));
    }

    public function __toString()
    {
        return strval($this->id);
    }

    public function toInt(): int
    {
        return $this->id;
    }
}
