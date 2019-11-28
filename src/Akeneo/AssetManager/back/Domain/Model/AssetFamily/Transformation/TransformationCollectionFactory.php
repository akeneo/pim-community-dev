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
    /** @var OperationFactory */
    private $operationFactory;

    public function __construct(OperationFactory $operationFactory)
    {
        $this->operationFactory = $operationFactory;
    }

    public function fromNormalized(array $normalizedTransformations): TransformationCollection
    {
        Assert::allIsArray($normalizedTransformations);

        return TransformationCollection::create(
            array_map(function (array $normalizedTransformation): Transformation {
                return $this->buildTransformation($normalizedTransformation);
            }, $normalizedTransformations)
        );
    }

    private function buildTransformation(array $normalizedTransformation): Transformation
    {
        Assert::keyExists($normalizedTransformation, 'source');
        Assert::isArray($normalizedTransformation['source']);
        Assert::keyExists($normalizedTransformation, 'target');
        Assert::isArray($normalizedTransformation['target']);
        Assert::keyExists($normalizedTransformation, 'operations');
        Assert::isArray($normalizedTransformation['operations']);
        Assert::allIsArray($normalizedTransformation['operations']);
        Assert::keyExists($normalizedTransformation, 'filename_prefix');
        Assert::string($normalizedTransformation['filename_prefix']);
        Assert::keyExists($normalizedTransformation, 'filename_suffix');
        Assert::string($normalizedTransformation['filename_suffix']);

        return Transformation::create(
            Source::createFromNormalized($normalizedTransformation['source']),
            Target::createFromNormalized($normalizedTransformation['target']),
            OperationCollection::create(
                array_map(
                    function (array $normalizedOperation): Operation {
                        return $this->buildOperation($normalizedOperation);
                    },
                    $normalizedTransformation['operations']
                )
            ),
            $normalizedTransformation['filename_prefix'],
            $normalizedTransformation['filename_suffix']
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
