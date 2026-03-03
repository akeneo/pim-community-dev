<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\SimpleSelectProperty;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateSimpleSelectHandler implements GeneratePropertyHandlerInterface
{
    public function __construct(
        private readonly PropertyProcessApplier $propertyProcessApplier
    ) {
    }

    public function getPropertyClass(): string
    {
        return SimpleSelectProperty::class;
    }

    public function __invoke(
        PropertyInterface $simpleSelectProperty,
        IdentifierGenerator $identifierGenerator,
        ProductProjection $productProjection,
        string $prefix
    ): string {
        Assert::isInstanceOf($simpleSelectProperty, SimpleSelectProperty::class);
        $normalizedData = $simpleSelectProperty->normalize();
        $value = $productProjection->value(
            $normalizedData['attributeCode'],
            $normalizedData['locale'] ?? null,
            $normalizedData['scope'] ?? null,
        );
        Assert::string($value);

        return $this->propertyProcessApplier->apply(
            $simpleSelectProperty->process(),
            $simpleSelectProperty->attributeCode(),
            $value,
            $identifierGenerator->target()->asString(),
            $prefix
        );
    }
}
