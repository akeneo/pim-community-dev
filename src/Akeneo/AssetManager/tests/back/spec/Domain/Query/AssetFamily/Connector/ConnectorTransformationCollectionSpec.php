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

namespace spec\Akeneo\AssetManager\Domain\Query\AssetFamily\Connector;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformation;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformationCollectionSpec
{
    function let()
    {
        $this->beConstructedWith([
            new ConnectorTransformation(
                TransformationCode::fromString('code'),
                Source::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
                Target::createFromNormalized(['attribute' => 'source', 'channel' => null, 'locale' => null]),
                OperationCollection::create([]),
                null,
                'suffix'
            ),
        ]);
    }

    function it_is_initializable()
    {
        $this->beAnInstanceOf(ConnectorTransformationCollection::class);
    }

    function it_can_be_normalized()
    {
        $this->normalize()->shouldReturn([
            [
                'code' => 'code',
                'source' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'target' => ['attribute' => 'source', 'channel' => null, 'locale' => null],
                'operations' => [],
                'filename_suffix' => 'suffix',
            ]
        ]);
    }
}
