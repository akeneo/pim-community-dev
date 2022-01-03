<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\tools\phpstan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Node\InClassNode;

class ExceptionSuffixRule implements Rule
{
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

        // Ignore if not an exception subclass
        if (!in_array(\Exception::class, $classReflection->getParentClassesNames(), true)) {
            return [];
        }

        $className = (string) $node->getOriginalNode()->name;

        if(\str_ends_with($className, 'Exception')) {
            return [];
        }

        $message = sprintf('Exception "%s" missing "Exception" suffix', $className);

        return [RuleErrorBuilder::message($message)->build()];
    }
}
