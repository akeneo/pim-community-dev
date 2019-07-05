<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class CompiledRule
{
    /** @var array */
    private $conditions;

    /** @var array */
    private $actions;

    public function __construct(array $conditions, array $actions)
    {
        $this->conditions = $conditions;
        $this->actions = $actions;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}
