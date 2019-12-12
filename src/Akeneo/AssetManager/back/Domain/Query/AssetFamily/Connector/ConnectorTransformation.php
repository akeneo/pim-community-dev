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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorTransformation
{
    /** @var TransformationCode */
    private $code;

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

    public function __construct(
        TransformationCode $code,
        Source $source,
        Target $target,
        OperationCollection $operations,
        ?string $filenamePrefix,
        ?string $filenameSuffix
    ) {
        $this->code = $code;
        $this->source = $source;
        $this->target = $target;
        $this->operations = $operations;
        $this->filenamePrefix = $filenamePrefix;
        $this->filenameSuffix = $filenameSuffix;
    }

    public function normalize(): array
    {
        return array_filter(
            [
                'code' => $this->code->normalize(),
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
