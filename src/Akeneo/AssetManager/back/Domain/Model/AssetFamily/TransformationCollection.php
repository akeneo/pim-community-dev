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

class TransformationCollection implements \Countable
{
    /** @var Transformation[] */
    private $transformations = [];

    /**
     * @param Transformation[] $transformations
     */
    private function __construct(array $transformations)
    {
        Assert::allIsInstanceOf($transformations, Transformation::class);
        foreach ($transformations as $transformation) {
            $this->add($transformation);
        }

        $this->transformations = $transformations;
    }

    public static function create(array $transformations): self
    {
        return new self($transformations);
    }

    public function normalize(): array
    {
        return array_map(function (Transformation $transformation) {
            return $transformation->normalize();
        }, $this->transformations);
    }

    private function add(Transformation $transformation)
    {
        foreach ($this->transformations as $existingTransformation) {
            if ($existingTransformation->getTarget()->equals($transformation->getTarget())) {
                throw new \InvalidArgumentException('You can not define 2 transformation with the same target');
            }

            if ($existingTransformation->getTarget()->equals($transformation->getSource()) ||
                $transformation->getTarget()->equals($existingTransformation->getSource())) {
                throw new \InvalidArgumentException('You can not define a transformation having a source as a target of another transformation');
            }
        }

        $this->transformations[] = $transformation;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function count(): int
    {
        return count($this->transformations);
    }
}
