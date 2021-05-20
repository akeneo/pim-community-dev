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

namespace Akeneo\AssetManager\Domain\Query\AssetFamily\Connector;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformationCollection
{
    /** @var ConnectorTransformation[] */
    private array $connectorTransformations = [];

    public function __construct(array $connectorTransformations)
    {
        Assert::allIsInstanceOf($connectorTransformations, ConnectorTransformation::class);
        $this->connectorTransformations = $connectorTransformations;
    }

    public function normalize(): array
    {
        return array_map(
            fn(ConnectorTransformation $connectorTransformation) => $connectorTransformation->normalize(),
            $this->connectorTransformations
        );
    }
}
