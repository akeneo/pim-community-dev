<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;

final class InvokableControllerRule extends AbstractControllerRule
{
    private const ERROR_MESSAGE = 'Controller must be invokable';

    /**
     * @param Class_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isInControllerNamespace($scope)) {
            return [];
        }

        // Skip abstract controllers
        if ($node->isAbstract()) {
            return [];
        }

        foreach ($node->getMethods() as $method) {
            if ('__invoke' === (string) $method->name) {
                return [];
            }
        }

        return [self::ERROR_MESSAGE];
    }
}
