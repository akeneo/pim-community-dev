<?php

declare(strict_types=1);

namespace Pim\Upgrade;

use Doctrine\Migrations\AbstractMigration;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;

class MigrationCoveredByIntegrationTestRule implements Rule
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
        if ($scope->getClassReflection()->getParentClass()?->getName() !== AbstractMigration::class) {
            return [];
        }

        $file = $scope->getFile();
        $basename = \basename($file, '.php');

        $integration = \sprintf('Pim\\Upgrade\\Schema\\Tests\\%s_Integration', $basename);

        if (!\class_exists($integration)) {
            return ['Migration must be covered by an integration test'];
        }

        return [];
    }
}
