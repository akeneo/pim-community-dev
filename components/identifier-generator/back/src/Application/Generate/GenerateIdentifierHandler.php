<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GeneratePropertyHandlerInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
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
        $identifierGenerator = $command->getIdentifierGenerator();
        $transformedDelimiter = $identifierGenerator->delimiter()->asString() ?? '';
        switch ($identifierGenerator->textTransformation()->normalize()) {
            case TextTransformation::UPPERCASE:
                $transformedDelimiter = \mb_strtoupper($transformedDelimiter);

                break;
            case TextTransformation::LOWERCASE:
                $transformedDelimiter = \mb_strtolower($transformedDelimiter);

                break;
        }

        $result = '';
        foreach ($identifierGenerator->structure()->getProperties() as $property) {
            if ('' !== $result) {
                $result .= $transformedDelimiter;
            }

            $generatedProperty = $this->generateProperty($property, $identifierGenerator, $command->getProductProjection(), $result);
            switch ($identifierGenerator->textTransformation()->normalize()) {
                case TextTransformation::UPPERCASE:
                    $generatedProperty = \mb_strtoupper($generatedProperty);

                    break;
                case TextTransformation::LOWERCASE:
                    $generatedProperty = \mb_strtolower($generatedProperty);

                    break;
            }

            $result .= $generatedProperty;
        }

        return $result;
    }

    private function generateProperty(
        PropertyInterface $property,
        IdentifierGenerator $identifierGenerator,
        ProductProjection $productProjection,
        string $prefix
    ): string {
        if (!isset($this->generateProperties[\get_class($property)])) {
            throw new \InvalidArgumentException(\sprintf('No generator found for property %s', \get_class($property)));
        }

        return ($this->generateProperties[\get_class($property)])($property, $identifierGenerator, $productProjection, $prefix);
    }
}
