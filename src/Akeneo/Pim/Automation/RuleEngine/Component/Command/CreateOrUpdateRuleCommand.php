<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\AddAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ClearAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ConcatenateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CopyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CustomAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Label;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\RemoveAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\SetAction;
use Webmozart\Assert\Assert;

class CreateOrUpdateRuleCommand
{
    public $code;
    public $priority;
    public $actions;
    public $conditions;
    public $labels;

    public function __construct(array $data)
    {
        $this->code = $data['code'] ?? null;
        $this->priority = $data['priority'] ?? null;

        $conditions = $data['content']['conditions'] ?? $data['conditions'] ?? null;
        if (is_array($conditions)) {
            $conditions = array_map(
                function ($condition) {
                    return is_array($condition) ? new Condition($condition) : $condition;
                },
                $conditions
            );
        }
        $this->conditions = $conditions;

        $actions = $data['content']['actions'] ?? $data['actions'] ?? null;
        if (is_array($actions)) {
            $actions = array_map(
                function ($action) {
                    return $this->createActionCommand($action);
                },
                $actions
            );
        }
        $this->actions = $actions;

        $labels = $data['labels'] ?? [];
        if (is_array($labels)) {
            $labels = array_map(
                function ($key, $value) {
                    return new Label($key, $value);
                },
                array_keys($labels),
                $labels
            );
        }
        $this->labels = $labels;
    }

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->code);
        Assert::nullOrInteger($this->priority);
        Assert::isArray($this->conditions);
        Assert::allIsInstanceOf($this->conditions, Condition::class);
        Assert::isArray($this->actions);
        Assert::allIsInstanceOf($this->actions, ActionInterface::class);
        Assert::isArray($this->labels);
        Assert::allIsInstanceOf($this->labels, Label::class);

        $normalized = [
            'code' => $this->code,
            'priority' => $this->priority ?? 0,
            'conditions' => array_map(function (Condition $condition): array {
                return $condition->toArray();
            }, $this->conditions),
            'actions' => array_map(function (ActionInterface $action): array {
                return $action->toArray();
            }, $this->actions),
        ];

        $normalizedLabels = [];
        foreach ($this->labels as $label) {
            $normalizedLabels[$label->locale] = $label->label;
        }
        $normalized['labels'] = new \ArrayObject($normalizedLabels);

        return $normalized;
    }

    private function createActionCommand($action)
    {
        if (!is_array($action)) {
            return $action;
        }

        $type = $action['type'] ?? null;

        switch ($type) {
            case 'add':
                return new AddAction($action);
            case 'calculate':
                return new CalculateAction($action);
            case 'clear':
                return new ClearAction($action);
            case 'concatenate':
                return new ConcatenateAction($action);
            case 'copy':
                return new CopyAction($action);
            case 'remove':
                return new RemoveAction($action);
            case 'set':
                return new SetAction($action);
            default:
                return new CustomAction($action);
        }
    }
}
