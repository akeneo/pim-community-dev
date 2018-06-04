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

namespace PimEnterprise\Component\SuggestData\Command;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SaveConfiguration
{
    /** @var string */
    private $code;

    /** @var array */
    private $configurationFields;

    /**
     * @param string $code
     * @param array  $configurationFields
     */
    public function __construct(string $code, array $configurationFields)
    {
        if (empty($code)) {
            throw new \InvalidArgumentException('Configuration code cannot be empty.');
        }
        $this->code = $code;

        $this->addConfigurationFields($configurationFields);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getConfigurationFields(): array
    {
        return $this->configurationFields;
    }

    /**
     * Validates and adds configuration fields.
     *
     * @throws \InvalidArgumentException
     *
     * @param array $configurationFields
     */
    private function addConfigurationFields(array $configurationFields): void
    {
        if (empty($configurationFields)) {
            throw new \InvalidArgumentException('Configuration fields cannot be empty.');
        }

        foreach ($configurationFields as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException(sprintf(
                    'The key of a configuration field must be a string, "%s" given.',
                    gettype($key)
                ));
            }
            if (!is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'The value of a configuration field must be a string, "%s" given.',
                    gettype($value)
                ));
            }
            if (empty($value)) {
                throw new \InvalidArgumentException('The value of a configuration field cannot be empty.');
            }

            $this->configurationFields[$key] = $value;
        }
    }
}
