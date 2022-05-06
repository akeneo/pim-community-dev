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

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformation
{
    public function __construct(
        private TransformationLabel $label,
        private Source $source,
        private Target $target,
        private OperationCollection $operations,
        private ?string $filenamePrefix,
        private ?string $filenameSuffix,
    ) {
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
            ],
            fn ($value) => null !== $value
        );
    }
}
