<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GeneratePropertyHandlerInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateIdentifierHandler
{
    /** @var array<string, GeneratePropertyHandlerInterface> */
    private array $generateProperties = [];

    /**
     * @param \Traversable<GeneratePropertyHandlerInterface> $generateProperties
     */
    public function __construct(
        \Traversable $generateProperties,
    ) {
        foreach ($generateProperties as $generateProperty) {
            Assert::isInstanceOf($generateProperty, GeneratePropertyHandlerInterface::class);
            $this->generateProperties[$generateProperty->getPropertyClass()] = $generateProperty;
        }
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
        if (!isset($this->generateProperties[\get_class($property)])) {
            throw new \InvalidArgumentException(\sprintf(
                'No generator found for property %s',
                \get_class($property)
            ));
        }

        return ($this->generateProperties[\get_class($property)])($property, $target, $prefix);
    }
}
