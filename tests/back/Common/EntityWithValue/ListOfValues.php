<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

use Doctrine\Common\Collections\ArrayCollection;

final class ListOfValues
{
    /** @var ArrayCollection */
    private $values;

    /**
     * @param array $values
     */
    private function __construct(array $values)
    {
        $this->values = new ArrayCollection($values);
    }

    /**
     * @return ListOfValues
     */
    public static function initialize(): ListOfValues
    {
        return new self([]);
    }

    /**
     * @param Code  $attribute
     * @param Value $value
     */
    public function add(Code $attribute, Value $value): void
    {
        $values = [$value];
        if (null !== $existingValues = $this->values->get((string) $attribute)) {
            $existingValues[] = $value;
            $values = $existingValues;
        }

        $this->values->set((string) $attribute, $values);
    }

    /**
     * @return array
     */
    public function toStandardFormat(): array
    {
        $values = [];
        foreach ($this->values as $attribute => $value) {
            foreach ($value as $byChannelAndLocale) {
                /** @var Value $byChannelAndLocale */
                $values[$attribute][] = $byChannelAndLocale->toStandardFormat();
            }
        }

        return $values;
    }
}
