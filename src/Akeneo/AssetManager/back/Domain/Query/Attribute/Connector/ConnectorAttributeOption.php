<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query\Attribute\Connector;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

class ConnectorAttributeOption
{
    private const OPTION_CODE = 'code';
    private const LABELS = 'labels';

    private OptionCode $code;

    private LabelCollection $labels;

    public function __construct(OptionCode $code, LabelCollection $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    public function normalize(): array
    {
        $normalizedLabels = $this->labels->normalize();

        return [
            self::OPTION_CODE => (string) $this->code,
            self::LABELS      => empty($normalizedLabels) ? (object) [] : $normalizedLabels
        ];
    }
}
