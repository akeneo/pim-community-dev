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

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\AddAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ClearAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\ConcatenateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CopyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Label;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\RemoveAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\SetAction;

final class CreateOrUpdateRuleCommand
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

        $conditions = $data['conditions'] ?? null;
        if (is_array($conditions)) {
            $conditions = array_map(function($condition) {
                return is_array($condition) ? new Condition($condition) : null;
            }, $conditions);
        }
        $this->conditions = $conditions;

        $actions = $data['actions'] ?? null;
        if (is_array($actions)) {
            $actions = array_map(function($action) {
                return $this->createActionCommand($action);
            }, $actions);
        }
        $this->actions = $actions;

        $labels = $data['labels'] ?? null;
        if (is_array($labels)) {
            $labels = array_map(function ($key, $value) {
                return new Label($key, $value);
            }, array_keys($labels), $labels);
        }
        $this->labels = $labels;
    }

    private function createActionCommand($action)
    {
        if (!is_array($action)) {
            return null;
        }

        $type = $action['type'] ?? null;

        switch($type) {
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
//            default: return new CreateCustomActionCommand($action);
        }
    }
}
