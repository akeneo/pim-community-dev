<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateAutoNumberHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateIdentifierCommandHandler
{
    public function __construct(
        private GenerateAutoNumberHandler $generateAutoNumber,
        private GenerateFreeTextHandler $generateFreeText,
    ) {
    }

    public function __invoke(
        GenerateIdentifierCommand $command,
    ): string {
        $delimiter = $command->getDelimiter();
        $properties = $command->getProperties();

        return \implode(
            $delimiter?->asString() ?? '',
            array_map(fn (PropertyInterface $property): string => $this->generateProperty($property), $properties)
        );
    }

    private function generateProperty(PropertyInterface $property): string
    {
        if ($property instanceof AutoNumber) {
            return ($this->generateAutoNumber)($property);
        }
        if ($property instanceof FreeText) {
            return ($this->generateFreeText)($property);
        }

        throw new \InvalidArgumentException(\sprintf(
            'No generator found for property %s',
            \get_class($property)
        ));
    }
}
