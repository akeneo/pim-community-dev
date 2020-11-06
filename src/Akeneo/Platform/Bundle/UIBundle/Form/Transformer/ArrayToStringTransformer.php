<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $filterUniqueValues;

    /**
     * @param string $delimiter
     * @param string $filterUniqueValues
     */
    public function __construct(string $delimiter, string $filterUniqueValues)
    {
        $this->delimiter = $delimiter;
        $this->filterUniqueValues = $filterUniqueValues;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value): string
    {
        if (null === $value || [] === $value) {
            return '';
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        return $this->transformArrayToString($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return $this->transformStringToArray($value);
    }

    /**
     * Transforms string to array
     *
     * @param string $stringValue
     */
    private function transformStringToArray(string $stringValue): array
    {
        $separator = trim($this->delimiter) !== '' ? trim($this->delimiter) : $this->delimiter;
        $arrayValue = explode($separator, $stringValue);
        return $this->filterArrayValue($arrayValue);
    }

    /**
     * Transforms array to string
     *
     * @param array $arrayValue
     */
    private function transformArrayToString(array $arrayValue): string
    {
        $separator = trim($this->delimiter) !== '' ? trim($this->delimiter) : $this->delimiter;
        return implode($separator, $this->filterArrayValue($arrayValue));
    }

    /**
     * Trims all elements and apply unique filter if needed
     *
     * @param array $arrayValue
     */
    private function filterArrayValue(array $arrayValue): array
    {
        if ($this->filterUniqueValues !== '') {
            $arrayValue = array_unique($arrayValue);
        }
        $arrayValue = array_filter(array_map('trim', $arrayValue));
        return array_values($arrayValue);
    }
}
