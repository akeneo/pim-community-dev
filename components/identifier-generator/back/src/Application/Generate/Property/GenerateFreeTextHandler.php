<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateFreeTextHandler implements GeneratePropertyHandler
{
    public function __invoke(PropertyInterface $freeText, Target $target, string $prefix): string
    {
        Assert::isInstanceOf($freeText, FreeText::class);

        return $freeText->asString();
    }

    public function supports(PropertyInterface $property): bool
    {
        return $property instanceof FreeText;
    }
}
