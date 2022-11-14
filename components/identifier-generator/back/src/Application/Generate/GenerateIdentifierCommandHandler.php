<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GeneratePropertyHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateIdentifierCommandHandler
{
    /**
     * @param GeneratePropertyHandler[] $generateProperties
     */
    public function __construct(
        private array $generateProperties,
    ) {
    }

    public function __invoke(
        GenerateIdentifierCommand $command,
    ): string {
        $delimiter = $command->getDelimiter();
        $properties = $command->getProperties();
        $target = $command->getTarget();

        $result = '';
        foreach ($properties as $property) {
            if ($result !== '') {
                $result .= $delimiter?->asString() ?? '';
            }

            $result .= $this->generateProperty($property, $target, $result);
        }

        return $result;
    }

    private function generateProperty(PropertyInterface $property, Target $target, string $prefix): string
    {
        foreach ($this->generateProperties as $generateProperty) {
            if ($generateProperty->supports($property)) {
                return ($generateProperty)($property, $target, $prefix);
            }
        }

        throw new \InvalidArgumentException(\sprintf(
            'No generator found for property %s',
            \get_class($property)
        ));
    }
}
