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
        Assert::false($source->equals($target), 'A transformation can not have the same source and target');

        $this->source = $source;
        $this->target = $target;
        $this->operations = $operations;
    }

    public static function create(Source $source, Target $target, OperationCollection $operations): self
    {
        return new self($source, $target, $operations);
    }

    public static function createFromNormalized(array $normalizedTransformation): self
    {
        Assert::keyExists($normalizedTransformation, 'source', '@TODO: message source');
        Assert::keyExists($normalizedTransformation, 'target', '@TODO: message target');
        Assert::keyExists($normalizedTransformation, 'operations', '@TODO: message operations');
        Assert::allIsArray($normalizedTransformation, '@TODO: message');

        return new self(
            Source::createFromNormalized($normalizedTransformation['source']),
            Target::createFromNormalized($normalizedTransformation['target']),
            OperationCollection::createFromNormalized($normalizedTransformation['operations'])
        );
    }

    public function getTarget(): Target
    {
        return $this->target;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function normalize(): array
    {
        return [
            'source' => $this->source->normalize(),
            'target' => $this->target->normalize(),
            'operations' => $this->operations->normalize()
        ];
    }
}
