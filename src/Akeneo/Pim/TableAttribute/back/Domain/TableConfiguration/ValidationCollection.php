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

use Webmozart\Assert\Assert;

final class ValidationCollection
{
    /** @var array<string, mixed> */
    private array $validations;

    /**
     * @param array<string, mixed> $validations
     */
    private function __construct(array $validations)
    {
        $this->validations = $validations;
    }

    /**
     * @param array<string, mixed>|\stdClass $validations
     */
    public static function fromNormalized($validations): ValidationCollection
    {
        if ($validations instanceof \stdClass) {
            $validations = [];
        }
        Assert::isArray($validations);
        Assert::allStringNotEmpty(array_keys($validations));

        return new self($validations);
    }

    /**
     * @return array<string, mixed>|\stdClass
     */
    public function normalize()
    {
        return 0 === count($this->validations) ? (object) [] : $this->validations;
    }
}
