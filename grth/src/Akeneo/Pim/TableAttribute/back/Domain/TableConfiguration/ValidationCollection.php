<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\TableValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\ValidationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Webmozart\Assert\Assert;

/**
 * @phpstan-implements \IteratorAggregate<string, TableValidation>
 */
final class ValidationCollection implements \IteratorAggregate
{
    /** @var array<string, TableValidation> */
    private array $validations = [];

    /**
     * @param array<string, TableValidation> $validations
     */
    private function __construct(array $validations)
    {
        Assert::allImplementsInterface($validations, TableValidation::class);
        $this->validations = $validations;
    }

    /**
     * @param array<string, mixed>|\stdClass $validations
     */
    public static function fromNormalized(ColumnDataType $dataType, $validations): ValidationCollection
    {
        if ($validations instanceof \stdClass) {
            $validations = [];
        }
        Assert::isArray($validations);
        Assert::allStringNotEmpty(array_keys($validations));

        $validationObjects = [];
        foreach ($validations as $key => $value) {
            $validationObjects[$key] = ValidationFactory::create($dataType, $key, $value);
        }

        return new self($validationObjects);
    }

    public static function createEmpty(): ValidationCollection
    {
        return new self([]);
    }

    /**
     * @return array<string, mixed>|\stdClass
     */
    public function normalize()
    {
        return 0 === count($this->validations) ? (object) [] : array_map(
            fn (TableValidation $validation) => $validation->getValue(),
            $this->validations
        );
    }

    /**
     * @return \Traversable<string, TableValidation>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->validations);
    }
}
