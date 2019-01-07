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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class OptionMapping
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /** @var string[] */
    private const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
    ];

    /** @var array */
    private $optionData;

    /**
     * @param array $optionData
     */
    public function __construct(array $optionData)
    {
        $this->validateOption($optionData);

        $this->optionData = $optionData;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->optionData['status'];
    }

    /**
     * @return string
     */
    public function getFranklinOptionId(): string
    {
        return $this->optionData['from']['id'];
    }

    /**
     * @return string
     */
    public function getFranklinOptionLabel(): string
    {
        return (!isset($this->optionData['from']['label']['en_US'])) ? '' : $this->optionData['from']['label']['en_US'];
    }

    /**
     * @return string
     */
    public function getPimOption(): ?string
    {
        return $this->optionData['to']['id'];
    }

    /**
     * @param array $optionData
     *
     * @throws \InvalidArgumentException
     */
    private function validateOption(array $optionData): void
    {
        $this->checkMandatoryKeys($optionData);
        $this->validateStatus($optionData['status']);

        if (!isset($optionData['from']['id']) || !is_string($optionData['from']['id'])) {
            throw new \InvalidArgumentException(
                sprintf('Missing "id" key in Franklin attribute option code data')
            );
        }

        if (null !== $optionData['to'] && (!isset($optionData['to']['id']) || !is_string($optionData['to']['id']))) {
            throw new \InvalidArgumentException(
                sprintf('Missing "id" key in PIM attribute option code data')
            );
        }
    }

    /**
     * @param array $optionData
     *
     * @throws \InvalidArgumentException
     */
    private function checkMandatoryKeys(array $optionData): void
    {
        $mandatoryKeys = [
            'from',
            'to',
            'status',
        ];

        foreach ($mandatoryKeys as $mandatoryKey) {
            if (!array_key_exists($mandatoryKey, $optionData)) {
                throw new \InvalidArgumentException(
                    sprintf('Missing key "%s" in attribute option', $mandatoryKey)
                );
            }
        }
    }

    /**
     * @param string $status
     *
     * @throws \InvalidArgumentException
     */
    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::STATUSES)) {
            throw new \InvalidArgumentException(
                sprintf('The attribute option status "%s" is invalid', $status)
            );
        }
    }
}
