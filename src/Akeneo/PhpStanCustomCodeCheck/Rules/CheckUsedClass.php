<?php

declare(strict_types=1);

namespace Akeneo\PhpStanCustomCodeCheck\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class CheckUsedClass implements Rule
{
	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\Use_::class;
	}

    public function processNode(Node $node, Scope $scope): array
    {
        foreach ($node->uses as $use) {
            echo "DEBUG node: ".$use->name."\n";
        }
        return [];
    }
}
