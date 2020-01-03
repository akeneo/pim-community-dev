<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Checker;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateValueChecker
{
    /**
     * This method checks that there is no duplicate value for the same scope and same locale
     * For example, this call is invalid:
     * {
     *   "identifier": "complete",
     *   "values": {
     *     "a_simple_select": [
     *       {"locale": null, "scope": null, "data": "optionB"},
     *       {"locale": null, "scope": null, "data": "optionA"}
     *     ]
     *   }
     * }
     *
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    public function check($data): void
    {
        if (!is_array($data) ||
            !isset($data['values']) ||
            !is_array($data['values'])
        ) {
            return;
        }

        foreach ($data['values'] as $attributeCode => $values) {
            $alreadyDefinedKeys = [];
            if (!is_array($values)) {
                return;
            }

            foreach ($values as $value) {
                $newKey = $this->generateKey(
                    isset($value['scope']) ? $value['scope'] : null,
                    isset($value['locale']) ? $value['locale'] : null
                );

                if (isset($alreadyDefinedKeys[$newKey])) {
                    throw new InvalidPropertyTypeException(
                        $attributeCode,
                        $newKey,
                        static::class,
                        sprintf('You cannot update the same product value on the "%s" attribute twice, with the same scope and locale.', $attributeCode)
                    );
                }
                $alreadyDefinedKeys[$newKey] = true;
            }
        }
    }

    private function generateKey(?string $channelCode, ?string $localeCode): string
    {
        $channelCode = null !== $channelCode ? $channelCode : '<all_channels>';
        $localeCode = null !== $localeCode ? $localeCode : '<all_locales>';
        $key = sprintf('%s-%s', $channelCode, $localeCode);

        return $key;
    }
}
