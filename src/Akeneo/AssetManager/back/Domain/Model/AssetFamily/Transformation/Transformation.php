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

use Webmozart\Assert\Assert;

class Transformation
{
    /** @var Source */
    private $source;

    /** @var Target */
    private $target;

    /** @var OperationCollection */
    private $operations;

    private function __construct(Source $source, Target $target, OperationCollection $operations)
    {
        Assert::false(
            $source->getAttributeIdentifierAsString() === $target->getAttributeIdentifierAsString() &&
            $source->getChannelReference()->equals($target->getChannelReference()) &&
            $source->getLocaleReference()->equals($target->getLocaleReference())
        );

        $this->source = $source;
        $this->target = $target;
        $this->operations = $operations;
    }

    public static function create(Source $source, Target $target, OperationCollection $operations): self
    {
        return new self($source, $target, $operations);
    }

    public function getTarget(): Target
    {
        return $this->target;
    }
}
