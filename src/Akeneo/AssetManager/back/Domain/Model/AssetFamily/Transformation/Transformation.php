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
    private TransformationLabel $label;

    private Source $source;

    private Target $target;

    private OperationCollection $operations;

    private ?string $filenamePrefix = null;

    private ?string $filenameSuffix = null;

    private \DateTimeInterface $updatedAt;

    private function __construct(
        TransformationLabel $label,
        Source $source,
        Target $target,
        OperationCollection $operations,
        ?string $filenamePrefix,
        ?string $filenameSuffix,
        \DateTimeInterface $updatedAt
    ) {
        Assert::false($source->equals($target), 'A transformation can not have the same source and target');

        Assert::stringNotEmpty(
            sprintf('%s%s', $filenamePrefix ?? '', $filenameSuffix ?? ''),
            'A transformation must have at least a filename prefix or a filename suffix'
        );

        $this->label = $label;
        $this->source = $source;
        $this->target = $target;
        $this->operations = $operations;
        $this->filenamePrefix = $filenamePrefix;
        $this->filenameSuffix = $filenameSuffix;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        TransformationLabel $label,
        Source $source,
        Target $target,
        OperationCollection $operations,
        ?string $filenamePrefix,
        ?string $filenameSuffix,
        \DateTimeInterface $updatedAt
    ): self {
        return new self($label, $source, $target, $operations, $filenamePrefix, $filenameSuffix, $updatedAt);
    }

    public function getLabel(): TransformationLabel
    {
        return $this->label;
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

    public function getFilenamePrefix(): ?string
    {
        return $this->filenamePrefix;
    }

    public function getFilenameSuffix(): ?string
    {
        return $this->filenameSuffix;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function normalize(): array
    {
        return array_filter(
            [
                'label' => $this->label->normalize(),
                'source' => $this->source->normalize(),
                'target' => $this->target->normalize(),
                'operations' => $this->operations->normalize(),
                'filename_prefix' => $this->filenamePrefix,
                'filename_suffix' => $this->filenameSuffix,
                'updated_at' => $this->updatedAt->format(\DateTimeInterface::ISO8601),
            ],
            fn ($value) => null !== $value
        );
    }

    public function equals(Transformation $transformation): bool
    {
        return $this->label->equals($transformation->getLabel())
            && $this->source->equals($transformation->getSource())
            && $this->target->equals($transformation->getTarget())
            && $this->operations->equals($transformation->getOperationCollection())
            && $this->filenamePrefix === $transformation->getFilenamePrefix()
            && $this->filenameSuffix === $transformation->getFilenameSuffix();
    }
}
