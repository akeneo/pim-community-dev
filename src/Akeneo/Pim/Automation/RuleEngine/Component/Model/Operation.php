<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Represents an operation used in a calculate action. Holds an operator (+, -, x, /) and an operand.
 */
final class Operation
{
    public const MULTIPLY = 'multiply';
    public const ADD = 'add';
    public const DIVIDE = 'divide';
    public const SUBTRACT = 'subtract';

    /** @var string */
    private $operator;

    /** @var Operand */
    private $operand;

    private function __construct(string $operator, Operand $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }

    public static function fromNormalized(array $data): self
    {
        Assert::keyExists($data, 'operator', 'Operation expects an "operator" key');
        Assert::string($data['operator']);
        Assert::oneOf(
            $data['operator'],
            [self::MULTIPLY, self::ADD, self::DIVIDE, self::SUBTRACT],
            sprintf(
                'Operation expects one of the following operators: %s',
                implode(', ', [self::MULTIPLY, self::ADD, self::DIVIDE, self::SUBTRACT])
            )
        );
        $operator = $data['operator'];
        unset($data['operator']);

        $operand = Operand::fromNormalized($data);
        if (self::DIVIDE === $operator && 0.0 === $operand->getConstantValue()) {
            throw new \InvalidArgumentException('Cannot accept a division by zero operation');
        }

        return new self($operator, $operand);
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getOperand(): Operand
    {
        return $this->operand;
    }
}
