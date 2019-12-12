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

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationFactory;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorTransformationCollectionHydrator;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformationCollectionHydratorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, OperationFactory $operationFactory)
    {
        $this->beConstructedWith($validator, $operationFactory);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ConnectorTransformationCollectionHydrator::class);
    }

    function it_hydrates_a_transformation_collection(
        ValidatorInterface $validator,
        OperationFactory $operationFactory,
        ConstraintViolationListInterface $violations
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('id');
        $transformations = [
            [
                'code' => 'code1',
                'source' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'thumbnail', 'parameters' => ['width' => 100, 'height' => 80]],
                ],
                'filename_prefix' => 'pre',
                'filename_suffix' => 'suffix',
                'updated_at' => '1990',
            ],
            [
                'code' => 'code2',
                'source' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target2', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => 'pref',
                'updated_at' => '1990',
            ],
        ];

        $validator->validate($transformations[0], new Transformation($assetFamilyIdentifier))
            ->willReturn($violations);
        $validator->validate($transformations[1], new Transformation($assetFamilyIdentifier))
            ->willReturn($violations);
        $violations->count()->willReturn(0, 0);

        $operationFactory->create('thumbnail', ['width' => 100, 'height' => 80])
            ->willReturn(ThumbnailOperation::create(['width' => 100, 'height' => 80]));

        $result = $this->hydrate($transformations, $assetFamilyIdentifier);
        $result->shouldBeAnInstanceOf(ConnectorTransformationCollection::class);
        $result->shouldBeLike(new ConnectorTransformationCollection([
            new ConnectorTransformation(
                TransformationCode::fromString('code1'),
                Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'target', 'channel' => null, 'locale' => null]),
                OperationCollection::create([
                    ThumbnailOperation::create(['width' => 100, 'height' => 80]),
                ]),
                'pre',
                'suffix'
            ),
            new ConnectorTransformation(
                TransformationCode::fromString('code2'),
                Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'target2', 'channel' => null, 'locale' => null]),
                OperationCollection::create([]),
                'pref',
                null
            ),
        ]));
    }

    function it_skips_invalid_transformations_while_hydrating_collection(
        ValidatorInterface $validator,
        ConstraintViolationListInterface $violationsForFirstTransformation,
        ConstraintViolationListInterface $violationsForSecondTransformation
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('id');
        $transformation = [
            [
                'code' => 'code1',
                'source' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target', 'channel' => null, 'locale' => null],
                'operations' => [
                    ['type' => 'thumbnail', 'parameters' => ['width' => 100, 'height' => 80]],
                ],
                'filename_prefix' => 'pre',
                'filename_suffix' => 'suffix',
                'updated_at' => '1990',
            ],
            [
                'code' => 'code2',
                'source' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'target2', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_prefix' => 'pref',
                'updated_at' => '1990',
            ],
        ];

        $validator->validate($transformation[0], new Transformation($assetFamilyIdentifier))
            ->willReturn($violationsForFirstTransformation);
        $validator->validate($transformation[1], new Transformation($assetFamilyIdentifier))
            ->willReturn($violationsForSecondTransformation);
        $violationsForFirstTransformation->count()->willReturn(1);
        $violationsForSecondTransformation->count()->willReturn(0);

        $result = $this->hydrate($transformation, $assetFamilyIdentifier);
        $result->shouldBeAnInstanceOf(ConnectorTransformationCollection::class);
        $result->shouldBeLike(new ConnectorTransformationCollection([
            new ConnectorTransformation(
                TransformationCode::fromString('code2'),
                Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'target2', 'channel' => null, 'locale' => null]),
                OperationCollection::create([]),
                'pref',
                null
            ),
        ]));
    }
}
