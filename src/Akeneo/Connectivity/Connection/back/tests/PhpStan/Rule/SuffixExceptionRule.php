<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;

final class SuffixExceptionRule implements Rule
{
    private const ERROR_MESSAGE = 'Exception must be suffixed with "Exception" exclusively';

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        $isAnExceptionSubclass = \in_array(\Exception::class, $classReflection->getParentClassesNames(), true);

        $className = (string) $node->getOriginalNode()->name;
        $doesNameEndWithException = \str_ends_with($className, 'Exception');

        if ($doesNameEndWithException && !$isAnExceptionSubclass) {
            return [self::ERROR_MESSAGE];
        }

        if (!$doesNameEndWithException && $isAnExceptionSubclass) {
            return [self::ERROR_MESSAGE];
        }

        return [];
    }
}
