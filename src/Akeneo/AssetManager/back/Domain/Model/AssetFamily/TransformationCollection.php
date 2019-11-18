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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Webmozart\Assert\Assert;

class TransformationCollection
{
    /** @var Transformation[] */
    private $transformations;

    private function __construct(array $transformations)
    {
        Assert::allIsInstanceOf(Transformation::class, $transformations);
        $this->transformations = $transformations;
    }

    public static function create(array $transformations): self
    {
        return new self($transformations);
    }
}
