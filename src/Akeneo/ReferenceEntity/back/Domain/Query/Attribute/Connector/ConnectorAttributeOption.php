<?php

namespace Akeneo\ReferenceEntity\Domain\Query\Attribute\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;

class ConnectorAttributeOption
{
    private const OPTION_CODE = 'code';
    private const LABELS = 'labels';

    /** @var OptionCode */
    private $code;

    /** @var LabelCollection */
    private $labels;

    public function __construct(OptionCode $code, LabelCollection $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    public function normalize(): array
    {
        return [
            self::OPTION_CODE => (string) $this->code,
            self::LABELS      => $this->labels->normalize()
        ];
    }
}
