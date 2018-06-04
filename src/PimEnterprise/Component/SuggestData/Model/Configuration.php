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

namespace PimEnterprise\Component\SuggestData\Model;

/**
 * Holds the configuration of a third party connector.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class Configuration
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
        $this->code = $code;
        $this->configurationFields = $configurationFields;
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
     * @param array $configurationFields
     */
    public function setConfigurationFields(array $configurationFields): void
    {
        $this->configurationFields = $configurationFields;
    }

    /**
     * Returns a normalized configuration.
     *
     * @return array
     */
    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'configuration_fields' => $this->configurationFields,
        ];
    }
}
