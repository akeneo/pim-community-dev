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
    private const SUPPORTED_OPERATORS = ['mul','div','add','sub'];

    /** @var string */
    private $operator;

    /** @var string */
    private $value;

    private function __construct(string $operator, string $value)
    {
        Assert::oneOf($operator, self::SUPPORTED_OPERATORS);
        Assert::numeric($value);

        $this->operator = $operator;
        $this->value = $value;
    }

    public static function create(string $operator, string $value): self
    {
        return new self($operator, $value);
    }

    public function normalize(): array
    {
        return ['operator' => $this->operator, 'value' => $this->value];
    }
}
