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
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class WarningCollection
{
    /** @var Warning[] */
    private $collection = [];

    /**
     * @param array $rawApiResponse
     */
    public function __construct(array $rawApiResponse)
    {
        $this->validateResponseFormat($rawApiResponse);
        $this->collection = $this->buildCollection($rawApiResponse);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $warnings = [];
        foreach ($this->collection as $warning) {
            $warnings[$warning->trackerId()] = $warning->message();
        }

        return $warnings;
    }

    /**
     * @param array $rawApiResponse
     *
     * @return array
     */
    private function buildCollection(array $rawApiResponse): array
    {
        $collection = [];
        foreach ($rawApiResponse['warnings'] as $rawSubscription) {
            $collection[] = new Warning($rawSubscription);
        }

        return $collection;
    }

    /**
     * @param array $rawApiResponse
     *
     * @throws \InvalidArgumentException
     */
    private function validateResponseFormat(array $rawApiResponse): void
    {
        if (!isset($rawApiResponse['warnings'])
            || !is_array($rawApiResponse['warnings'])
        ) {
            throw new \InvalidArgumentException('Missing "warnings" key in API response');
        }
    }
}
