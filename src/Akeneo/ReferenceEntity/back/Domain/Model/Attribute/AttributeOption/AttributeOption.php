<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOption
{
    private const OPTION_CODE = 'option_code';
    private const LABELS = 'labels';

    /** @var OptionCode */
    private $optionCode;

    /** @var LabelCollection */
    private $labels;

    private function __construct(OptionCode $optionCode, LabelCollection $labels)
    {
        $this->optionCode = $optionCode;
        $this->labels = $labels;
    }

    public static function create(OptionCode $optionCode, LabelCollection $labelCollection): self
    {
        return new self($optionCode, $labelCollection);
    }

    public function normalize(): array
    {
        return [
            self::OPTION_CODE => (string) $this->optionCode,
            self::LABELS      => $this->labels->normalize()
        ];
    }
}
