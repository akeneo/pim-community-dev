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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use Webmozart\Assert\Assert;

class TransformationCollection implements \IteratorAggregate
{
    /** @var Transformation[] */
    private $transformations = [];

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
            function (Transformation $transformation) {
                return $transformation->normalize();
            },
            $this->transformations
        ));
    }

    public static function noTransformation(): self
    {
        return new self([]);
    }

    public function getByTransformationCode(TransformationCode $code): ?Transformation
    {
        return $this->transformations[$code->toString()] ?? null;
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

            if ($existingTransformation->getCode()->equals($transformation->getCode())) {
                throw new \InvalidArgumentException(sprintf(
                    'You cannot define two transformations with the same code "%s"',
                    $transformation->getCode()->toString()
                ));
            }
        }

        $this->transformations[$transformation->getCode()->toString()] = $transformation;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->transformations);
    }

    public function update(TransformationCollection $transformationCollection): void
    {
        foreach ($this->transformations as $code => $currentTransformation) {
            $findInNewCollection = $transformationCollection->getByTransformationCode(
                TransformationCode::fromString($code)
            );
            if (null === $findInNewCollection) {
                $this->removeTransformation($code);
                continue;
            }

            if ($currentTransformation->equals($findInNewCollection)) {
                continue;
            }

            $this->updateTransformation($findInNewCollection);
        }

        /** @var Transformation $newTransformation */
        foreach ($transformationCollection as $newTransformation) {
            $transformationCode = $newTransformation->getCode();
            if (null === $this->getByTransformationCode($transformationCode)) {
                $this->add($newTransformation);
            }
        }
    }

    private function updateTransformation(Transformation $transformation): void
    {
        $this->transformations[$transformation->getCode()->toString()] = $transformation;
    }

    private function removeTransformation(string $code): void
    {
        unset($this->transformations[$code]);
    }
}
