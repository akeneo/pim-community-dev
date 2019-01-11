<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class AttributeOptions implements \IteratorAggregate
{
    /** @var array */
    private $options;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->validateOptions($options);

        foreach ($options as $franklinOptionId => $option) {
            $this->options[$franklinOptionId] = new AttributeOptionMappingRequest(
                $option['franklinAttributeOptionCode']['label'],
                $option['catalogAttributeOptionCode'],
                (int) $option['status']
            );
        }
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->options);
    }

    public function getCatalogOptionCodes(): array
    {
        return array_map(function ($option) {
            return $option->getPimAttributeOptionCode();
        }, array_values($this->options));
    }

    /**
     * @param array $options
     */
    private function validateOptions(array $options): void
    {
        $this->validateOptionsAreNotEmpty($options);
        $this->validateExternalAttributeCodeIds($options);
        $this->validateAttributeKeys($options);
    }

    /**
     * @param array $options
     */
    private function validateOptionsAreNotEmpty(array $options): void
    {
        if (empty($options)) {
            throw new \InvalidArgumentException('Options cannot be an empty array');
        }
    }

    /**
     * @param array $options
     */
    private function validateExternalAttributeCodeIds(array $options): void
    {
        if (count(array_keys($options)) !== count(array_filter(array_keys($options)))) {
            throw new \InvalidArgumentException('One or multiple Franklin attribute option ids are missing');
        }
    }

    /**
     * @param array $options
     */
    private function validateAttributeKeys(array $options): void
    {
        $expectedKeys = [
            'franklinAttributeOptionCode',
            'catalogAttributeOptionCode',
            'status',
        ];

        foreach ($options as $option) {
            foreach ($expectedKeys as $expectedKey) {
                if (!array_key_exists($expectedKey, $option)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Missing key %s in attribute option data',
                        $expectedKey
                    ));
                }
            }
        }
    }
}
