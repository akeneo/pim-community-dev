<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Operation
{
    public const STANDARD_OPERATOR = 'mul';
    public const STANDARD_VALUE = '1';
    private const SUPPORTED_OPERATORS = [self::STANDARD_OPERATOR, 'div', 'add', 'sub'];

    private string $operator;

    private string $value;

    private function __construct(string $operator, string $value)
    {
        Assert::oneOf($operator, self::SUPPORTED_OPERATORS);
        Assert::regex(
            $value,
            '~^[0-9]*\.?[0-9]+$~',
            sprintf('Expecting operation value to be a numeric, "%s" given', $value)
        );

        $this->operator = $operator;
        $this->value = $value;
    }

    public static function create(string $operator, string $value): self
    {
        return new self($operator, $value);
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function normalize(): array
    {
        return ['operator' => $this->operator, 'value' => $this->value];
    }
}
