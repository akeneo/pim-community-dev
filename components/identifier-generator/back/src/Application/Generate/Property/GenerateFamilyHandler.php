<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateFamilyHandler implements GeneratePropertyHandlerInterface
{
    public function __construct(
        private readonly PropertyProcessApplier $processApplier,
    ) {
    }

    public function __invoke(
        PropertyInterface $familyProperty,
        IdentifierGenerator $identifierGenerator,
        ProductProjection $productProjection,
        string $prefix
    ): string {
        Assert::isInstanceOf($familyProperty, FamilyProperty::class);
        Assert::string($productProjection->familyCode());

        return $this->processApplier->apply(
            $familyProperty->process(),
            'family',
            $productProjection->familyCode(),
            $identifierGenerator->target()->asString(),
            $prefix
        );
    }

    public function getPropertyClass(): string
    {
        return FamilyProperty::class;
    }
}
