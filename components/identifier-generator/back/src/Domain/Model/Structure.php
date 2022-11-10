<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
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

    /**
     * @param PropertyInterface[] $properties
     * @return static
     */
    public static function fromArray(array $properties): self
    {
        Assert::notEmpty($properties);
        Assert::allIsInstanceOf($properties, PropertyInterface::class);

        return new self($properties);
    }

    /**
     * @return array<array<string, int|string>>
     */
    public function normalize(): array
    {
        return \array_map(static fn (PropertyInterface $property) => $property->normalize(), $this->properties);
    }

    /**
     * @param array<array<string, int|string>> $normalizedValues
     * @return static
     */
    public static function fromNormalized(array $normalizedValues): self
    {
        $properties = [];
        foreach ($normalizedValues as $normalizedValue) {
            Assert::isMap($normalizedValue);
            Assert::stringNotEmpty($normalizedValue['type'] ?? null);
            $properties[] = match ($normalizedValue['type']) {
                FreeText::type() => FreeText::fromNormalized($normalizedValue),
                AutoNumber::type() => AutoNumber::fromNormalized($normalizedValue),
                default => throw new \InvalidArgumentException(sprintf('The type %s does not exist', $normalizedValue['type'])),
            };
        }

        return self::fromArray($properties);
    }

    /**
     * @return PropertyInterface[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function generate(?Delimiter $delimiter): string
    {
        return \implode(
            $delimiter?->asString() ?? '',
            array_map(fn (PropertyInterface $property): string => $property->generate(), $this->properties)
        );
    }
}
