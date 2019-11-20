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
    // TODO ATR-27: constructor, ...
    private $operationResolver;

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

        return Transformation::create(
            Source::createFromNormalized($normalizedTransformation['source']),
            Target::createFromNormalized($normalizedTransformation['target']),
            OperationCollection::create(
                []
                /* TODO: uncomment after ATR-27
                array_map(
                    function (array $normalizedOperation): Operation {
                        return $this->buildOperation($normalizedOperation);
                    },
                    $normalizedTransformation['operations']
                )
                */
            )
        );
    }

    private function buildOperation(array $normalizedOperation): Operation
    {
        Assert::keyExists($normalizedOperation, 'type');
        $parameters = $normalizedOperation['parameters'] ?? [];
        Assert::isArray($parameters);

        $operationClass = $this->operationResolver->resolve($normalizedOperation['type']);

        return $operationClass::create($parameters);
    }
}
