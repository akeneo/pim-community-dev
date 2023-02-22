<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Type\ObjectType;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InvalidLazyCommandRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return (string|RuleError)[] errors
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!(new ObjectType('Symfony\Component\Console\Command\Command'))->isSuperTypeOf(
            $scope->getType($node->var)
        )->yes()) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier || $node->name->name !== 'setName') {
            return [];
        }

        return ['Symfony Commands should be lazy. See https://symfony.com/blog/new-in-symfony-3-4-lazy-commands'];
    }
}
