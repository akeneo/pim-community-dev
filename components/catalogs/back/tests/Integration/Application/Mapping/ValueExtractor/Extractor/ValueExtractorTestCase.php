<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Application\Mapping\ValueExtractor\Extractor;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ValueExtractorInterface;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ValueExtractorTestCase extends IntegrationTestCase
{
    private const TARGET_TYPES_INTERFACES_MAPPING = [
        'number' => NumberValueExtractorInterface::class,
        'string' => StringValueExtractorInterface::class,
    ];
    private const TARGET_TYPES_RETURN_TYPES_MAPPING = [
        'number' => ['null', 'float', 'int'],
        'string' => ['null', 'string'],
    ];

    protected function assertExtractorReturnTypeIsConsistent(ValueExtractorInterface $extractor): void
    {
        // check implemented interface
        $this->assertInstanceOf(
            self::TARGET_TYPES_INTERFACES_MAPPING[$extractor->getSupportedTargetType()],
            $extractor,
        );

        // or check the return type (more complicated but prevent covriance)
        $extractMethodSignature = new \ReflectionMethod($extractor, 'extract');

        $returnType = $extractMethodSignature->getReturnType();
        $this->assertNotNull($returnType);

        $this->assertEqualsCanonicalizing(
            self::TARGET_TYPES_RETURN_TYPES_MAPPING[$extractor->getSupportedTargetType()],
            $this->flattenReturnTypes($returnType),
        );
    }

    /**
     * @return array<string>
     */
    private function flattenReturnTypes(\ReflectionType $type): array
    {
        $returnTypes = [];

        if ($type instanceof \ReflectionIntersectionType) {
            foreach ($type->getTypes() as $innerType) {
                $returnTypes[] = $this->flattenReturnTypes($innerType);
            }

            $returnTypes = \array_merge(...$returnTypes);
        } elseif ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $innerType) {
                $returnTypes[] = $this->flattenReturnTypes($innerType);
            }

            $returnTypes = \array_merge(...$returnTypes);
        } elseif ($type instanceof \ReflectionNamedType) {
            $returnTypes[] = $type->getName();
        } else {
            throw new \LogicException('Unknown Reflection type');
        }

        if ($type->allowsNull()) {
            $returnTypes[] = 'null';
        }

        return $returnTypes;
    }
}
