<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateFamilyHandler implements GeneratePropertyHandlerInterface
{
    public function __invoke(
        PropertyInterface $familyProperty,
        Target $target,
        ProductProjection $productProjection,
        string $prefix
    ): string {
        Assert::isInstanceOf($familyProperty, FamilyProperty::class);

        switch ($familyProperty->process()->type()) {
            case Process::PROCESS_TYPE_TRUNCATE:
                if ($familyProperty->process()->operator() === Process::PROCESS_OPERATOR_EQ) {
                    try {
                        Assert::minLength($productProjection->familyCode(), $familyProperty->process()->value());
                    } catch (\InvalidArgumentException) {
                        throw new UnableToTruncateException(
                            sprintf('%s%s', $prefix, $productProjection->familyCode()),
                            $target->asString(),
                            $productProjection->familyCode()
                        );
                    }
                }

                return \substr($productProjection->familyCode(), 0, $familyProperty->process()->value());
            case Process::PROCESS_TYPE_NO:
            default:
                return $productProjection->familyCode();
        }
    }

    public function getPropertyClass(): string
    {
        return FamilyProperty::class;
    }
}
