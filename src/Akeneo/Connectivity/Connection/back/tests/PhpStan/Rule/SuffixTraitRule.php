<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

final class SuffixTraitRule implements Rule
{
    private const ERROR_MESSAGE = 'Trait must be suffixed with "Trait" exclusively';

    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (\str_ends_with((string) $node->name, 'Trait')) {
            if (!$node instanceof Trait_) {
                return [self::ERROR_MESSAGE];
            }

            return [];
        }

        if ($node instanceof Trait_) {
            return [self::ERROR_MESSAGE];
        }

        return [];
    }
}
