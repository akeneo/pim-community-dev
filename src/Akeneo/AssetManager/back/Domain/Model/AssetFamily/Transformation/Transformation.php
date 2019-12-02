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

    /** @var ?string */
    private $filenamePrefix;

    /** @var ?string */
    private $filenameSuffix;

    private function __construct(
        Source $source,
        Target $target,
        OperationCollection $operations,
        ?string $filenamePrefix,
        ?string $filenameSuffix
    ) {
        Assert::false($source->equals($target), 'A transformation can not have the same source and target');

        Assert::stringNotEmpty(
            sprintf('%s%s', $filenamePrefix ?? '', $filenameSuffix ?? ''),
            'A transformation must have at least a filename prefix or a filename suffix'
        );

        $this->source = $source;
        $this->target = $target;
        $this->operations = $operations;
        $this->filenamePrefix = $filenamePrefix;
        $this->filenameSuffix = $filenameSuffix;
    }

    public static function create(
        Source $source,
        Target $target,
        OperationCollection $operations,
        ?string $filenamePrefix,
        ?string $filenameSuffix
    ): self {
        return new self($source, $target, $operations, $filenamePrefix, $filenameSuffix);
    }

    public function getTarget(): Target
    {
        return $this->target;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    public function getOperationCollection(): OperationCollection
    {
        return $this->operations;
    }

    public function normalize(): array
    {
        return array_filter(
            [
                'source' => $this->source->normalize(),
                'target' => $this->target->normalize(),
                'operations' => $this->operations->normalize(),
                'filename_prefix' => $this->filenamePrefix,
                'filename_suffix' => $this->filenameSuffix,
            ],
            function ($value) {
                return null !== $value;
            }
        );
    }
}
