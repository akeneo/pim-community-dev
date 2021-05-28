<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption;

use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOption
{
    private const OPTION_CODE = 'code';
    private const LABELS = 'labels';

    private OptionCode $code;

    private LabelCollection $labels;

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
