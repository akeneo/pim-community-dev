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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

/**
 * Holds the configuration of data provider.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class Configuration
{
    public const PIM_AI_CODE = 'pim-ai';

    /** @var array */
    private $values;

    /**
     * @param array  $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return null|string
     */
    public function getToken(): ?string
    {
        if (array_key_exists('token', $this->values)) {
            return $this->values['token'];
        }

        return null;
    }

    /**
     * @param array $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    /**
     * Returns a normalized configuration.
     *
     * @return array
     */
    public function normalize(): array
    {
        return [
            'code' => static::PIM_AI_CODE,
            'values' => $this->values,
        ];
    }
}
