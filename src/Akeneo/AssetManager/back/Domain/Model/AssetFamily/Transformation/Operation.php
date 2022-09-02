<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

interface Operation
{
    /**
     * Returns the type of the operation.
     */
    public static function getType(): string;

    /**
     * Returns an operation object from array parameters.
     */
    public static function create(array $parameters): Operation;

    public function normalize(): array;
}
