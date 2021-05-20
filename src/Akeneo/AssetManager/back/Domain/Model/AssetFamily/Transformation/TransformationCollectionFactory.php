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

use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Webmozart\Assert\Assert;

class TransformationCollectionFactory
{
    private OperationFactory $operationFactory;

    public function __construct(OperationFactory $operationFactory)
    {
        $this->operationFactory = $operationFactory;
    }

    public function fromNormalized(array $normalizedTransformations): TransformationCollection
    {
        Assert::allIsArray($normalizedTransformations);

        return TransformationCollection::create(
            array_map(fn(array $normalizedTransformation): Transformation => $this->buildTransformation($normalizedTransformation), $normalizedTransformations)
        );
    }

    /**
     * Use only this method when data comes from storage. For any other scenario (specially when data comes from
     * external) the fromNormalized method should be used instead.
     *
     * @param array $normalizedTransformations
     * @return TransformationCollection
     */
    public function fromDatabaseNormalized(array $normalizedTransformations): TransformationCollection
    {
        Assert::allIsArray($normalizedTransformations);

        return TransformationCollection::create(
            array_filter(array_map(function (array $normalizedTransformation): ?Transformation {
                try {
                    return $this->buildTransformation($normalizedTransformation);
                } catch (UnknownOperationException $e) {
                    // We only catch exception if an operation is unknown (for example someone deactivate an operation).
                    // Catching other exceptions can hide other problems.
                    return null;
                }
            }, $normalizedTransformations))
        );
    }

    private function buildTransformation(array $normalizedTransformation): Transformation
    {
        Assert::keyExists($normalizedTransformation, 'label');
        Assert::stringNotEmpty($normalizedTransformation['label']);
        Assert::keyExists($normalizedTransformation, 'source');
        Assert::isArray($normalizedTransformation['source']);
        Assert::keyExists($normalizedTransformation, 'target');
        Assert::isArray($normalizedTransformation['target']);
        Assert::keyExists($normalizedTransformation, 'operations');
        Assert::isArray($normalizedTransformation['operations']);
        Assert::allIsArray($normalizedTransformation['operations']);
        Assert::nullOrString($normalizedTransformation['filename_prefix'] ?? null);
        Assert::nullOrString($normalizedTransformation['filename_suffix'] ?? null);
        Assert::stringNotEmpty($normalizedTransformation['updated_at']);

        return Transformation::create(
            TransformationLabel::fromString($normalizedTransformation['label']),
            Source::createFromNormalized($normalizedTransformation['source']),
            Target::createFromNormalized($normalizedTransformation['target']),
            OperationCollection::create(
                array_map(
                    fn(array $normalizedOperation): Operation => $this->buildOperation($normalizedOperation),
                    $normalizedTransformation['operations']
                )
            ),
            $normalizedTransformation['filename_prefix'] ?? null,
            $normalizedTransformation['filename_suffix'] ?? null,
            new \DateTimeImmutable($normalizedTransformation['updated_at'])
        );
    }

    private function buildOperation(array $normalizedOperation): Operation
    {
        Assert::keyExists($normalizedOperation, 'type');
        Assert::stringNotEmpty($normalizedOperation['type']);
        Assert::keyExists($normalizedOperation, 'parameters');
        Assert::isArray($normalizedOperation['parameters']);

        return $this->operationFactory->create($normalizedOperation['type'], $normalizedOperation['parameters']);
    }
}
