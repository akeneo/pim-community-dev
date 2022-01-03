<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\tools\phpstan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class TraitSuffixRule implements Rule
{
    public function getNodeType(): string
    {
        return Trait_::class;
    }

    /**
     * @param Trait_ $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if(\str_ends_with((string) $node->name, 'Trait')) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Trait missing "Trait" suffix')->build()
        ];
    }
}
