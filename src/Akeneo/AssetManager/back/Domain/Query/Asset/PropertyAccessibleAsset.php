<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Query\Asset;

/**
 * Read Model to get the Asset values for a pattern that would be formatted like this attributeCode-channel-locale
 *
 * Ex :
 * {
 *   'code' => 'iphone4s',
 *   'values' => [
 *     'description-ecommerce-en_us' => 'The brand new iphone 4s',
 *     'tags-en_US' => ['Iphone 4S', 'Apple'],
 *     'name-ecommerce' => 'Iphone 4S'
 *    ]
 * }
 *
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class PropertyAccessibleAsset
{
    public string $code;

    public array $values;

    private const CODE_NAME = 'code';

    public function __construct(string $code, array $values)
    {
        $this->code = $code;
        $this->values = $values;
    }

    public function hasValue(string $pattern): bool
    {
        if (self::CODE_NAME === $pattern) {
            return true;
        }

        return isset($this->values[$pattern]);
    }

    /**
     * @return mixed
     */
    public function getValue(string $pattern)
    {
        if (self::CODE_NAME === $pattern) {
            return $this->code;
        }

        return $this->values[$pattern];
    }
}
