<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\tools\phpstan\Rule;

use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PhpParser\Node;
use PHPStan\Rules\RuleErrorBuilder;

class InterfaceSuffixRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\Interface_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if(\str_ends_with((string) $node->name, 'Interface')) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Interface missing "Interface" suffix')->build()
        ];
    }
}
