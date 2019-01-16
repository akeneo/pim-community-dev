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
    private const OPTION_CODE = 'code';
    private const LABELS = 'labels';

    /** @var OptionCode */
    private $code;

    /** @var LabelCollection */
    private $labels;

    private function __construct(OptionCode $code, LabelCollection $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
    }

    public static function create(OptionCode $optionCode, LabelCollection $labelCollection): self
    {
        return new self($optionCode, $labelCollection);
    }

    public function updateLabels(LabelCollection $labelCollection)
    {
        $labels = $this->labels->normalize();
        $updatedLabels = $labelCollection->normalize();
        $this->labels = LabelCollection::fromArray(array_merge($labels, $updatedLabels));
    }

    public function normalize(): array
    {
        return [
            self::OPTION_CODE => (string) $this->code,
            self::LABELS      => $this->labels->normalize()
        ];
    }

    public function getCode(): OptionCode
    {
        return $this->code;
    }
}
