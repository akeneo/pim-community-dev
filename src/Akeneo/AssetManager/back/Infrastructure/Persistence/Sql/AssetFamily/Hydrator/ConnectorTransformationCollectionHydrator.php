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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Hydrate collection of transformation(s) coming from the storage to public format.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformationCollectionHydrator
{
    private ValidatorInterface $validator;

    private OperationFactory $operationFactory;

    public function __construct(ValidatorInterface $validator, OperationFactory $operationFactory)
    {
        $this->validator = $validator;
        $this->operationFactory = $operationFactory;
    }

    /**
     * When we hydrate we also check the validity of each transformation.
     * If a transformation is no more valid (for example the target attribute does not exist anymore) then we skip it.
     * This way we return only valid data to end user.
     *
     * @param array                 $transformations
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @return ConnectorTransformationCollection
     */
    public function hydrate(
        array $transformations,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): ConnectorTransformationCollection {
        $connectorTransformations = [];

        foreach ($transformations as $transformation) {
            $violations = $this->validator->validate($transformation, new Transformation($assetFamilyIdentifier));
            if (count($violations) > 0) {
                continue;
            }

            $connectorTransformations[] = new ConnectorTransformation(
                TransformationLabel::fromString($transformation['label']),
                Source::createFromNormalized($transformation['source']),
                Target::createFromNormalized($transformation['target']),
                $this->buildOperations($transformation['operations']),
                $transformation['filename_prefix'] ?? null,
                $transformation['filename_suffix'] ?? null
            );
        }

        return new ConnectorTransformationCollection($connectorTransformations);
    }

    private function buildOperations(array $normalizedOperations): OperationCollection
    {
        return OperationCollection::create(
            array_map(fn(array $normalizedOperation): Operation => $this->operationFactory->create(
                $normalizedOperation['type'],
                $normalizedOperation['parameters']
            ), $normalizedOperations)
        );
    }
}
