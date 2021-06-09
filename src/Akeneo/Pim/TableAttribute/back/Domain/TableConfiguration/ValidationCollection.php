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
    private array $validations;

    private function __construct(array $validations)
    {
        $this->validations = $validations;
    }

    public static function fromNormalized(array $validations): ValidationCollection
    {
        Assert::allStringNotEmpty(array_keys($validations));

        return new self($validations);
    }

    public function normalize(): array
    {
        return $this->validations;
    }
}
