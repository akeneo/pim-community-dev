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

    /**
     * @param Transformation[] $transformations
     */
    private function __construct(array $transformations)
    {
        Assert::allIsInstanceOf($transformations, Transformation::class);
        Assert::false(
            self::hasDuplicateTarget($transformations),
            'You can not define 2 transformation with the same target'
        );
        Assert::false(
            self::sourceIsTarget($transformations),
            'You can not define a transformation having a source as a target of another transformation'
        );

        $this->transformations = $transformations;
    }

    public static function create(array $transformations): self
    {
        return new self($transformations);
    }

    /**
     * @param Transformation[] $transformations
     * @return bool
     */
    private static function hasDuplicateTarget(array $transformations): bool
    {
        foreach ($transformations as $transformation1) {
            foreach ($transformations as $transformation2) {
                if ($transformation1 !== $transformation2) {
                    $target1 = $transformation1->getTarget();
                    $target2 = $transformation2->getTarget();
                    if ($target1->getAttributeIdentifierAsString() === $target2->getAttributeIdentifierAsString() &&
                        $target1->getChannelReference()->equals($target2->getChannelReference()) &&
                        $target1->getLocaleReference()->equals($target2->getLocaleReference())
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Transformation[] $transformations
     * @return bool
     */
    private static function sourceIsTarget(array $transformations): bool
    {
        foreach ($transformations as $transformation1) {
            foreach ($transformations as $transformation2) {
                if ($transformation1 !== $transformation2) {
                    $target = $transformation1->getTarget();
                    $source = $transformation2->getSource();
                    if ($target->getAttributeIdentifierAsString() === $source->getAttributeIdentifierAsString() &&
                        $target->getChannelReference()->equals($source->getChannelReference()) &&
                        $target->getLocaleReference()->equals($source->getLocaleReference())
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
