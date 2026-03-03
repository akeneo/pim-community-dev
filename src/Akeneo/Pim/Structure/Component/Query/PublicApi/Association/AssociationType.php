<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Association;

class AssociationType
{
    private string $code;
    private LabelCollection $labels;
    private bool $isTwoWay;
    private bool $isQuantified;

    public function __construct(string $code, LabelCollection $labels, bool $isTwoWay, bool $isQuantified)
    {
        $this->code = $code;
        $this->labels = $labels;
        $this->isTwoWay = $isTwoWay;
        $this->isQuantified = $isQuantified;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(string $localeCode): string
    {
        return $this->labels->getLabel($localeCode);
    }

    public function isTwoWay(): bool
    {
        return $this->isTwoWay;
    }

    public function isQuantified(): bool
    {
        return $this->isQuantified;
    }
}
