<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class OptionValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    private const LABEL_LOCALE_KEY = 'label_locale';
    private const SEPARATOR = ', ';

    /** @var GetExistingAttributeOptionsWithValues */
    private $getExistingAttributeOptionsWithValues;

    public function __construct(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues,
        array $attributeTypes
    ) {
        parent::__construct($attributeTypes);
        $this->getExistingAttributeOptionsWithValues = $getExistingAttributeOptionsWithValues;
    }

    public function stringify(ValueInterface $value, array $options = []): string
    {
        if (!$value instanceof OptionValueInterface && !$value instanceof OptionsValueInterface) {
            throw new \InvalidArgumentException(sprintf(
                'The value must an instance of %s, or %s, % given',
                OptionValueInterface::class,
                OptionsValueInterface::class,
                get_class($value)
            ));
        }

        $attributeCode = $value->getAttributeCode();
        $optionCodes = (array) $value->getData();

        // If a label is not provided in options we must use the code, so we can think
        // we don't have to call the query. We decide to do the query in any case in order to filter
        // the non existing options.
        $valuesByKeys = $this->getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes(
            array_map(function (string $optionCode) use ($attributeCode): string {
                return sprintf('%s.%s', $attributeCode, $optionCode);
            }, $optionCodes)
        );

        $localeCode = $options[static::LABEL_LOCALE_KEY] ?? null;
        $strings = [];
        foreach ($optionCodes as $optionCode) {
            $key = sprintf('%s.%s', $attributeCode, $optionCode);
            if (!array_key_exists($key, $valuesByKeys)) {
                continue;
            }

            $strings[] = null === $localeCode
                ? $optionCode
                : $valuesByKeys[$key][$localeCode] ?? $optionCode;
        }

        $strings = array_filter($strings, function (string $string): bool {
            return '' !== $string;
        });

        return implode(static::SEPARATOR, $strings);
    }
}
