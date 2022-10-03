<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Webmozart\Assert\Assert;

/**
 * Array of properties used to define the structure of an identifier generator
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Structure
{
    /**
     * @param PropertyInterface[] $properties
     */
    private function __construct(
        private array $properties,
    ) {
    }

    public static function fromArray(array $properties): self
    {
        Assert::notEmpty($properties);
        Assert::allIsInstanceOf($properties, PropertyInterface::class);

        return new self($properties);
    }

    /**
     * @return PropertyInterface[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
