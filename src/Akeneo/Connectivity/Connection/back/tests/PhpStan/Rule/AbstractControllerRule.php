<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

abstract class AbstractControllerRule implements Rule
{
    public function getNodeType(): string
    {
        return Class_::class;
    }

    abstract public function processNode(Node $node, Scope $scope): array;

    protected function isInControllerNamespace(Scope $scope): bool
    {
        return 1 === \preg_match('~Akeneo\\\Connectivity\\\Connection\\\Infrastructure\\\([^\\\]+\\\)*Controller(\\\.+)*~', $scope->getNamespace());
    }
}
