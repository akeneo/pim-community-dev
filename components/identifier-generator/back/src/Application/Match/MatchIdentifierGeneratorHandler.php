<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchConditionHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchIdentifierGeneratorHandler
{
    /** @var array<string, MatchConditionHandler> */
    private array $conditionMatchers = [];

    /**
     * @param \Traversable<MatchConditionHandler> $conditionMatchers
     */
    public function __construct(
        \Traversable $conditionMatchers,
    ) {
        foreach ($conditionMatchers as $conditionMatcher) {
            Assert::isInstanceOf($conditionMatcher, MatchConditionHandler::class);
            $this->conditionMatchers[$conditionMatcher->getConditionClass()] = $conditionMatcher;
        }
    }

    public function __invoke(MatchIdentifierGeneratorQuery $query): bool
    {
        $identifierGenerator = $query->identifierGenerator();
        $productProjection = $query->productProjection();
        $conditionsWithImplicitOnes = $identifierGenerator->conditions()->and($identifierGenerator->getImplicitConditions());

        return \array_reduce(
            $conditionsWithImplicitOnes->conditions(),
            fn (bool $prev, $condition): bool => $prev && $this->matchCondition($condition, $productProjection),
            true
        );
    }

    private function matchCondition(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        if (!isset($this->conditionMatchers[\get_class($condition)])) {
            throw new \InvalidArgumentException(\sprintf('No matcher found for condition %s', \get_class($condition)));
        }

        return ($this->conditionMatchers[\get_class($condition)])($condition, $productProjection);
    }
}
