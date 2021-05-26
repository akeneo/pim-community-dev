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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Webmozart\Assert\Assert;

class TransformationCollection implements \IteratorAggregate
{
    /** @var Transformation[] */
    private array $transformations = [];

    private function __construct(array $transformations)
    {
        Assert::allIsInstanceOf($transformations, Transformation::class);
        foreach ($transformations as $transformation) {
            $this->add($transformation);
        }
    }

    public static function create(array $transformations): self
    {
        return new self($transformations);
    }

    public function normalize(): array
    {
        return array_values(array_map(
            fn (Transformation $transformation) => $transformation->normalize(),
            $this->transformations
        ));
    }

    public static function noTransformation(): self
    {
        return new self([]);
    }

    public function getByTarget(Target $target): ?Transformation
    {
        foreach ($this->transformations as $transformation) {
            if ($transformation->getTarget()->equals($target)) {
                return $transformation;
            }
        }

        return null;
    }

    private function add(Transformation $transformation)
    {
        foreach ($this->transformations as $existingTransformation) {
            if ($existingTransformation->getTarget()->equals($transformation->getTarget())) {
                throw new \InvalidArgumentException('You can not define 2 transformation with the same target');
            }

            if ($existingTransformation->getTarget()->equals($transformation->getSource()) ||
                $transformation->getTarget()->equals($existingTransformation->getSource())) {
                throw new \InvalidArgumentException(
                    'You can not define a transformation having a source as a target of another transformation'
                );
            }
        }

        $this->transformations[] = $transformation;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->transformations);
    }

    public function update(TransformationCollection $transformationCollection): void
    {
        foreach ($this->transformations as $index => $currentTransformation) {
            $findInNewCollection = $transformationCollection->getByTarget(
                $currentTransformation->getTarget()
            );
            if (null === $findInNewCollection) {
                $this->removeTransformation($index);
                continue;
            }

            if ($currentTransformation->equals($findInNewCollection)) {
                continue;
            }

            $this->updateTransformation($index, $findInNewCollection);
        }

        /** @var Transformation $newTransformation */
        foreach ($transformationCollection as $newTransformation) {
            if (null === $this->getByTarget($newTransformation->getTarget())) {
                $this->add($newTransformation);
            }
        }
    }

    private function updateTransformation(int $index, Transformation $transformation): void
    {
        $this->transformations[$index] = $transformation;
    }

    private function removeTransformation(int $index): void
    {
        unset($this->transformations[$index]);
    }
}
