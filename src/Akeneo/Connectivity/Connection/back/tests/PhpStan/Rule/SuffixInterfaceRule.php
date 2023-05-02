<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class SuffixInterfaceRule implements Rule
{
    private const ERROR_MESSAGE = 'Interface must be suffixed with "Interface" exclusively';

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassLike) {
            return [];
        }

        if (\str_ends_with((string) $node->name, 'Interface')) {
            if (!$node instanceof Interface_) {
                return [self::ERROR_MESSAGE];
            }

            return [];
        }

        if ($node instanceof Interface_) {
            return [self::ERROR_MESSAGE];
        }

        return [];
    }
}
